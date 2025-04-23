<?php

namespace App\Service;

use App\Entity\Employee;
use App\Entity\Event;
use DateTime;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class NotificationService
{
    private MailerInterface $mailer;
    private string $vapidPublicKey;
    private string $vapidPrivateKey;

    public function __construct(MailerInterface $mailer, string $vapidPublicKey, string $vapidPrivateKey)
    {
        $this->mailer = $mailer;
        $this->vapidPublicKey = $vapidPublicKey;
        $this->vapidPrivateKey = $vapidPrivateKey;
    }

    public function sendMeetingReminder(Employee $employee, Event $event, int $minutesBefore = 60): void
    {
        $timeZoneOffset = $event->getMeetingRoom()->getOffice()->getTimeZone();

        $timeStart = clone $event->getTimeStart();
        $timeStart->modify("+$timeZoneOffset hours");

        $startTimeUtc = new \DateTime('now', new \DateTimeZone('UTC'));
        $startTimeUtc->setDate(
            $event->getDate()->format('Y'),
            $event->getDate()->format('m'),
            $event->getDate()->format('d')
        );
        $startTimeUtc->setTime(
            $timeStart->format('H'),
            $timeStart->format('i'),
            $timeStart->format('s')
        );

        $startTimeLocal = clone $startTimeUtc;
        $formattedTime = $startTimeLocal->format('H:i d.m.Y');

        $email = (new Email())
            ->from('no-reply@yourapp.com')
            ->to($employee->getEmail())
            ->subject('Напоминание о событии: ' . $event->getName())
            ->html("                <p>Напоминаем, что через " . $minutesBefore . " минут начнётся событие:</p>
                <p><strong>" . $event->getName() . "</strong></p>
                <p>Описание: " . $event->getDescription() . "</p>
                <p>Время: " . $formattedTime . " (часовой пояс: UTC" . ($timeZoneOffset > 0 ? '+' : '') . $timeZoneOffset . ")</p>
                <p>Комната: " . $event->getMeetingRoom()->getName() . "</p>
                <p>Организатор: " . $event->getAuthor()->getFullName() . "</p>
            ");

        $this->mailer->send($email);

        $this->sendPushNotification($employee, $event, "Событие '{$event->getName()}' начнётся через {$minutesBefore} минут.");
    }

    public function sendMeetingSummary(Employee $employee, Event $event): void
    {
        $timeZoneOffset = $event->getMeetingRoom()->getOffice()->getTimeZone();

        $timeEnd = clone $event->getTimeEnd();
        $timeEnd->modify("+$timeZoneOffset hours");

        $endTimeUtc = new \DateTime('now', new \DateTimeZone('UTC'));
        $endTimeUtc->setDate(
            $event->getDate()->format('Y'),
            $event->getDate()->format('m'),
            $event->getDate()->format('d')
        );
        $endTimeUtc->setTime(
            $timeEnd->format('H'),
            $timeEnd->format('i'),
            $timeEnd->format('s')
        );

        $endTimeLocal = clone $endTimeUtc;
        $formattedTime = $endTimeLocal->format('H:i d.m.Y');

        $email = (new Email())
            ->from('no-reply@yourapp.com')
            ->to($employee->getEmail())
            ->subject('Событие завершено: ' . $event->getName())
            ->html("                <p>Событие завершено:</p>
                <p><strong>" . $event->getName() . "</strong></p>
                <p>Описание: " . $event->getDescription() . "</p>
                <p>Время окончания: " . $formattedTime . " (часовой пояс: UTC" . ($timeZoneOffset > 0 ? '+' : '') . $timeZoneOffset . ")</p>
                <p>Комната: " . $event->getMeetingRoom()->getName() . "</p>
                <p>Организатор: " . $event->getAuthor()->getFullName() . "</p>
            ");

        $this->mailer->send($email);
    }

    public function sendPushNotification(Employee $employee, Event $event, string $message): void
    {
        $webPush = new WebPush([
            'VAPID' => [
                'subject' => 'mailto:no-reply@yourapp.com',
                'publicKey' => $this->vapidPublicKey ?: '',
                'privateKey' => $this->vapidPrivateKey ?: '',
            ],
        ]);

        if ($employee->getPushSubscriptions()->isEmpty()) {
            error_log("No push subscriptions for {$employee->getEmail()}");
            return;
        }

        foreach ($employee->getPushSubscriptions() as $subscription) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $subscription->getEndpoint(),
                    'publicKey' => $subscription->getP256dhKey(),
                    'authToken' => $subscription->getAuthToken(),
                ]),
                json_encode([
                    'title' => 'Напоминание о событии: ' . $event->getName(),
                    'body' => $message,
                    //'icon' => '/path/to/icon.png',
                    //'data' => ['url' => '/event/' . $event->getId()], // Ссылка на событие
                ])
            );
        }

        foreach ($webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                error_log("Push failed for {$report->getEndpoint()}: {$report->getReason()}");
            } else {
                error_log("Push sent successfully to {$report->getEndpoint()}");
            }
        }
    }
}