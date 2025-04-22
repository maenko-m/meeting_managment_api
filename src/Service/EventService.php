<?php

namespace App\Service;

use App\DTO\EventCreateDTO;
use App\DTO\EventUpdateDTO;
use App\Entity\Event;
use App\Entity\Employee;
use App\Entity\MeetingRoom;
use App\Enum\RecurrenceType;
use App\Enum\Status;
use App\Interface\EventServiceInterface;
use App\Message\SendNotificationMessage;
use App\Repository\EventRepository;
use App\Service\NotificationSchedulerService;
use DateInterval;
use DatePeriod;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Security\Core\User\UserInterface;

class EventService implements EventServiceInterface
{
    private EntityManagerInterface $em;
    private EventRepository $eventRepository;
    private MessageBusInterface $bus;
    private NotificationSchedulerService $notificationSchedulerService;

    public function __construct(EntityManagerInterface $em, EventRepository $eventRepository, MessageBusInterface $bus, NotificationSchedulerService $notificationSchedulerService)
    {
        $this->em = $em;
        $this->eventRepository = $eventRepository;
        $this->bus = $bus;
        $this->notificationSchedulerService = $notificationSchedulerService;
    }

    public function getAllEvents(array $filters, ?UserInterface $user): array
    {
        $name = $filters['name'] ?? null;
        $room_id = $filters['room_id'] ?? null;
        $type = $filters['type'] ?? null;
        $descOrder = filter_var($filters['desc_order'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $date = $filters['date'] ?? null;
        $isArchived = $filters['archived'] ?? null;
        $office_id = $filters['office_id'] ?? null;
        //$page = $filters['page'] ?? 1;
        //$limit = $filters['limit'] ?? 10;

        return $this->eventRepository->getAllByFilter($room_id, $type, $name, $user, $descOrder, $isArchived, $date, $office_id, /*$page, $limit*/);
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

        if ($dto->recurrenceType && $dto->recurrenceInterval) {
            $event->setRecurrenceType($dto->getRecurrenceTypeEnum());
            $event->setRecurrenceInterval($dto->recurrenceInterval);
        }

        if ($dto->recurrenceEnd) {
            $event->setRecurrenceEnd(DateTime::createFromFormat('Y-m-d', $dto->recurrenceEnd));
        }

        $timeZoneOffset = $meetingRoom->getOffice()->getTimeZone();

        $timeStart = DateTime::createFromFormat('H:i:s', $dto->timeStart);
        $timeStart->modify("-$timeZoneOffset hours");
        $event->setTimeStart($timeStart);

        $timeEnd = DateTime::createFromFormat('H:i:s', $dto->timeEnd);
        $timeEnd->modify("-$timeZoneOffset hours");
        $event->setTimeEnd($timeEnd);

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

        //уведомления
        $this->notificationSchedulerService->scheduleNotifications($event);

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

        $timeZoneOffset = $event->getMeetingRoom()->getOffice()->getTimeZone();

        if ($dto->timeStart) {
            $timeStart = DateTime::createFromFormat('H:i:s', $dto->timeStart);
            $timeStart->modify("-$timeZoneOffset hours");
            $event->setTimeStart($timeStart);
        }
        if ($dto->timeEnd) {
            $timeEnd = DateTime::createFromFormat('H:i:s', $dto->timeEnd);
            $timeEnd->modify("-$timeZoneOffset hours");
            $event->setTimeEnd($timeEnd);
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

        $currentEmployees = $event->getEmployees();
        $newEmployeeIds = $dto->employeeIds;

        $currentEmployeeIds = $currentEmployees->map(fn(Employee $employee) => $employee->getId())->toArray();

        foreach ($newEmployeeIds as $employeeId) {
            if (!in_array($employeeId, $currentEmployeeIds)) {
                $employee = $this->em->getRepository(Employee::class)->find($employeeId);
                if ($employee) {
                    $event->addEmployee($employee);
                }
            }
        }

        foreach ($currentEmployees as $employee) {
            if (!in_array($employee->getId(), $newEmployeeIds)) {
                $event->removeEmployee($employee);
            }
        }

        $this->em->flush();

        //уведомления
        $this->notificationSchedulerService->scheduleNotifications($event);

        return $event;
    }

    public function deleteEvent(int $eventId): void
    {
        $event = $this->em->getRepository(Event::class)->find($eventId);

        if (!$event) {
            throw new NotFoundHttpException('Event not found');
        }

        $this->notificationSchedulerService->clearEventQueue($event->getId());

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