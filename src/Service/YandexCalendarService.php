<?php

namespace App\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Sabre\DAV\Client;
use Sabre\VObject\Component\VCalendar;
use App\Entity\MeetingRoom;
use App\Entity\Event;
use SimpleXMLElement;

class YandexCalendarService
{
    private Client $client;
    private string $yandexUsername;
    private EntityManagerInterface $em;

    public function __construct(string $yandexUsername, string $yandexPass, EntityManagerInterface $entityManager)
    {
        $this->client = new Client([
            'baseUri' => 'https://caldav.yandex.ru/',
            'userName' => $yandexUsername,
            'password' => $yandexPass,
            'curl' => [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ],
        ]);
        $this->yandexUsername = $yandexUsername;
        $this->em = $entityManager;
    }

    public function createCalendarForRoom(MeetingRoom $room): ?string
    {
        $calendarId = $room->getCalendarCode();
        if (!$calendarId) {
            $calendarPath = "/calendars/{$this->yandexUsername}/1";


            $xml = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<C:mkcalendar xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">
    <D:set>
        <D:prop>
            <D:displayname>{$room->getName()}</D:displayname>
        </D:prop>
    </D:set>
</C:mkcalendar>
XML;

            $response = $this->client->request('MKCALENDAR', $calendarPath, $xml, [
                'Content-Type' => 'application/xml',
            ]);
            $calendarId = $this->getCalendarCode($room);
            $room->setCalendarCode($calendarId);
            $this->em->flush();
        }
        return $calendarId;
    }

    private function getCalendarCode(MeetingRoom $room): ?string
    {
        $propfindXml = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<D:propfind xmlns:D="DAV:">
    <D:prop>
        <D:resourcetype />
        <D:displayname />
    </D:prop>
</D:propfind>
XML;

        $response = $this->client->request('PROPFIND', "/calendars/{$this->yandexUsername}/", $propfindXml, [
            'Depth' => 1,
            'Content-Type' => 'application/xml',
        ]);
        $body = $response['body'];

        $calendarCode = null;

        try {
            $xmlDoc = new SimpleXMLElement($body);
            $xmlDoc->registerXPathNamespace('D', 'DAV:');
            $xmlDoc->registerXPathNamespace('C', 'urn:ietf:params:xml:ns:caldav');

            $calendars = $xmlDoc->xpath('//D:response[D:propstat/D:prop/D:resourcetype/C:calendar]');


            foreach ($calendars as $calendar) {
                $href = (string)$calendar->href;
                $propstat = $calendar->children('D', true)->propstat;

                if ($propstat) {
                    $prop = $propstat->children('D', true)->prop;
                    if ($prop) {
                        $displayName = trim((string)$prop->children('D', true)->displayname);
                        if ($displayName === $room->getName()) {
                            $decodedHref = urldecode($href);

                            $calendarCode = trim(str_replace("/calendars/{$this->yandexUsername}/", '', $decodedHref), '/');
                        }
                    }
                }
            }
        }
        catch (\Exception $e) {
            return $calendarCode;
        }

        return $calendarCode;
    }

    public function syncEvent(Event $event): void
    {
        $room = $event->getMeetingRoom();
        $calendarId = $this->createCalendarForRoom($room);
        $calendarPath = "/calendars/{$this->yandexUsername}/{$calendarId}/";

        $rrule = null;
        $freq = null;

        $freqMap = [
            'day' => 'DAILY',
            'week' => 'WEEKLY',
            'month' => 'MONTHLY',
            'year' => 'YEARLY',
        ];

        if ($event->getRecurrenceType()) {
            $freq = $freqMap[$event->getRecurrenceType()->value] ?? null;
        }

        if ($freq && $event->getRecurrenceInterval()) {
            $rruleParts = [
                'FREQ=' . $freq,
                'INTERVAL=' . (int) $event->getRecurrenceInterval(),
            ];

            if ($event->getRecurrenceEnd() instanceof \DateTimeInterface) {
                $until = (clone $event->getRecurrenceEnd())->setTime(23, 59, 59);
                $rruleParts[] = 'UNTIL=' . $until->format('Ymd\THis\Z');
            }

            $rrule = implode(';', $rruleParts);
        }

        $vCalendar = new VCalendar();

        $vCalendar->add('VEVENT', [
                'SUMMARY' => $event->getName(),
                'DESCRIPTION' => $event->getDescription(),
                'DTSTART' => $event->getDate()->format('Ymd') . 'T' . $event->getTimeStart()->format('His') . 'Z',
                'DTEND' => $event->getDate()->format('Ymd') . 'T' . $event->getTimeEnd()->format('His') . 'Z',
                'UID' => 'event_' . $event->getId(),
            ] + ($rrule ? ['RRULE' => $rrule] : []));

        $eventPath = $calendarPath . 'event_' . $event->getId() . '.ics';

        $response = $this->client->request('PUT', $eventPath, $vCalendar->serialize(), [
            'Content-Type' => 'text/calendar',
        ]);
    }

    public function deleteEvent(Event $event): void
    {
        $room = $event->getMeetingRoom();
        $calendarId = $room->getCalendarCode();
        $eventPath = "/calendars/{$this->yandexUsername}/{$calendarId}/event_" . $event->getId() . ".ics";

        $response = $this->client->request('DELETE', $eventPath);
    }
}