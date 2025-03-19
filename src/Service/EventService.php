<?php

namespace App\Service;

use App\DTO\EventCreateDTO;
use App\DTO\EventUpdateDTO;
use App\Entity\Event;
use App\Entity\Employee;
use App\Entity\MeetingRoom;
use App\Enum\Status;
use App\Interface\EventServiceInterface;
use App\Repository\EventRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class EventService implements EventServiceInterface
{
    private EntityManagerInterface $em;
    private EventRepository $eventRepository;
    private YandexCalendarService  $yandexCalendarService;

    public function __construct(EntityManagerInterface $em, EventRepository $eventRepository, YandexCalendarService $yandexCalendarService)
    {
        $this->em = $em;
        $this->eventRepository = $eventRepository;
        $this->yandexCalendarService = $yandexCalendarService;
    }

    public function getAllEvents(array $filters, ?UserInterface $user): array
    {
        $name = $filters['name'] ?? null;
        $room_id = $filters['room_id'] ?? null;
        $type = $filters['type'] ?? null;
        $descOrder = $filters['desc_order'] ?? false;
        $page = $filters['page'] ?? 1;
        $limit = $filters['limit'] ?? 10;

        return $this->eventRepository->getAllByFilter($room_id, $type, $name, $user, $descOrder, $page, $limit);
    }

    public function getAllEventsByDate(DateTime $date): array
    {
        return $this->eventRepository->getAllByDate($date);
    }

    public function getEventById(int $eventId): ?Event
    {
        return $this->em->getRepository(Event::class)->find($eventId);
    }

    public function createEvent(EventCreateDTO $dto): Event
    {
        $author = $this->em->getRepository(Employee::class)->find($dto->authorId);
        $meetingRoom = $this->em->getRepository(MeetingRoom::class)->find($dto->meetingRoomId);

        if (!$meetingRoom) {
            throw new NotFoundHttpException('Meeting room not found');
        }

        if ($meetingRoom->getStatus() !== Status::ACTIVE) {
            throw new BadRequestHttpException('Meeting room is not active');
        }

        if (!$author) {
            throw new NotFoundHttpException('Author not found');
        }

        $event = (new Event())
            ->setName($dto->name)
            ->setDescription($dto->description)
            ->setDate(DateTime::createFromFormat('Y-m-d', $dto->date))
            ->setTimeStart(DateTime::createFromFormat('H:i:s', $dto->timeStart))
            ->setTimeEnd(DateTime::createFromFormat('H:i:s', $dto->timeEnd))
            ->setAuthor($author)
            ->setMeetingRoom($meetingRoom)
        ;

        if (!$this->validateEvent($event->getMeetingRoom(), $event->getDate(), $event->getTimeStart(), $event->getTimeEnd())) {
            throw new BadRequestHttpException('Event data uncorrected');
        }

        foreach ($dto->employeeIds as $employeeId) {
            $employee = $this->em->getRepository(Employee::class)->find($employeeId);
            if ($employee) {
                $event->addEmployee($employee);
            }
        }

        $this->em->persist($event);
        $this->em->flush();

        return $event;
    }

    public function updateEvent(int $id, EventUpdateDTO $dto): Event
    {
        $event = $this->em->getRepository(Event::class)->find($id);

        if (!$event) {
            throw new NotFoundHttpException('Event not found');
        }

        if ($dto->name) {
            $event->setName($dto->name);
        }
        if ($dto->description) {
            $event->setDescription($dto->description);
        }
        if ($dto->date) {
            $event->setDate(DateTime::createFromFormat('Y-m-d', $dto->date));
        }
        if ($dto->timeStart) {
            $event->setTimeStart(DateTime::createFromFormat('H:i:s', $dto->timeStart));
        }
        if ($dto->timeEnd) {
            $event->setTimeEnd(DateTime::createFromFormat('H:i:s', $dto->timeEnd));
        }

        if ($dto->authorId) {
            $author = $this->em->getRepository(Employee::class)->find($dto->authorId);
            if (!$author) {
                throw new NotFoundHttpException('Author not found');
            }
            $event->setAuthor($author);
        }

        if ($dto->meetingRoomId) {
            $meetingRoom = $this->em->getRepository(MeetingRoom::class)->find($dto->meetingRoomId);
            if (!$meetingRoom) {
                throw new NotFoundHttpException('Meeting room not found');
            }
            if ($meetingRoom->getStatus() !== Status::ACTIVE) {
                throw new BadRequestHttpException('Meeting room is not active');
            }
            $event->setMeetingRoom($meetingRoom);
        }

        if (!$this->validateEvent($event->getMeetingRoom(), $event->getDate(), $event->getTimeStart(), $event->getTimeEnd(), $event->getId())) {
            throw new BadRequestHttpException('Event data uncorrected');
        }

        $this->em->flush();

        return $event;
    }

    public function deleteEvent(int $eventId): void
    {
        $event = $this->em->getRepository(Event::class)->find($eventId);

        if (!$event) {
            throw new NotFoundHttpException('Event not found');
        }

        $this->em->remove($event);
        $this->em->flush();
    }

    private function validateEvent(MeetingRoom $room, \DateTimeInterface $date, \DateTimeInterface $timeStart, \DateTimeInterface $timeEnd, int $ignoreEventId = null): bool
    {
        if ($timeStart >= $timeEnd) {
            return false;
        }

        $conflictingEvents = $this->eventRepository->getConflictingEvents($room, $date, $timeStart, $timeEnd, $ignoreEventId);

        if (!empty($conflictingEvents)) {
            return false;
        }

        return true;
    }
}