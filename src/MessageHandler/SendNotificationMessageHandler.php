<?php

namespace App\MessageHandler;

use App\Entity\Event;
use App\Enum\RecurrenceType;
use App\Message\SendNotificationMessage;
use App\Repository\EventRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
class SendNotificationMessageHandler
{
    public function __construct(
        private readonly EventRepository        $eventRepository,
        private readonly NotificationService    $notificationService,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface    $bus,
    ) {}

    public function __invoke(SendNotificationMessage $message): void
    {
        $event = $this->eventRepository->find($message->eventId);
        if (!$event) {
            return;
        }

        foreach ($event->getEmployees() as $employee) {
            if ($message->type === 'reminder') {
                $this->notificationService->sendMeetingReminder($employee, $event, 60);
            } elseif ($message->type === 'summary') {
                $this->notificationService->sendMeetingSummary($employee, $event);

                // Обработка повтора
                if ($event->getRecurrenceType()) {
                    $this->handleRecurringEvent($event);
                }
            }
        }
    }

    private function handleRecurringEvent(Event $event): void
    {
        // Если есть тип повторения, создаем новое мероприятие
        $recurrenceUntil = $event->getRecurrenceEnd();

        // Проверяем, до какой даты повторяется событие (не создаем событие, если оно не повторяется больше)
        if ($recurrenceUntil && $recurrenceUntil <= new \DateTime('now', new \DateTimeZone('UTC'))) {
            return; // Если до текущей даты событие не повторяется, выходим
        }

        // Генерируем новое событие для повторения
        $newEvent = $this->generateNextRecurringEvent($event);

        // Сохраняем новое повторяющееся событие в базе данных
        $this->entityManager->persist($newEvent);
        $this->entityManager->flush();

        // Планируем уведомления для нового события (задержка перед уведомлением)
        $this->scheduleEventNotifications($newEvent);
    }

    private function generateNextRecurringEvent(Event $event): Event
    {
        // Клонируем оригинальное событие для следующего повтора
        $nextEvent = clone $event;

        // Устанавливаем повторяющееся событие с новым временем
        // Например, добавляем 1 день или 1 неделю в зависимости от типа повторения
        if ($event->getRecurrenceType() === RecurrenceType::DAY) {
            $nextEvent->setDate($event->getDate()->modify('+1 day'));
        } elseif ($event->getRecurrenceType() === RecurrenceType::WEEK) {
            $nextEvent->setDate($event->getDate()->modify('+1 week'));
        } elseif ($event->getRecurrenceType() === RecurrenceType::MONTH) {
            $nextEvent->setDate($event->getDate()->modify('+1 month'));
        } elseif ($event->getRecurrenceType() === RecurrenceType::YEAR) {
            $nextEvent->setDate($event->getDate()->modify('+1 year'));
        }

        // Устанавливаем поле для связи с родительским мероприятием
        $nextEvent->setRecurrenceParent($event); // Запоминаем ID родительского события

        return $nextEvent;
    }

    private function scheduleEventNotifications(Event $event): void
    {
        // Создаем уведомления для нового события
        $date = $event->getDate();
        $timeStart = $event->getTimeStart();
        $timeEnd = $event->getTimeEnd();

        $start = new \DateTime('now', new \DateTimeZone('UTC'));
        $start->setDate(
            (int)$date->format('Y'),
            (int)$date->format('m'),
            (int)$date->format('d')
        );
        $start->setTime(
            (int)$timeStart->format('H'),
            (int)$timeStart->format('i'),
            (int)$timeStart->format('s')
        );

        $end = clone $start;
        $end->setTime(
            (int)$timeEnd->format('H'),
            (int)$timeEnd->format('i'),
            (int)$timeEnd->format('s')
        );

        $now = time();

        // Отправка напоминания за 60 минут до начала
        $reminderTimestamp = $start->getTimestamp() - 60 * 60;
        if ($reminderTimestamp > $now) {
            $this->bus->dispatch(
                new SendNotificationMessage($event->getId(), 'reminder'),
                [new DelayStamp(($reminderTimestamp - $now) * 1000)]
            );
        }

        // Отправка уведомления после окончания
        $summaryTimestamp = $end->getTimestamp();
        if ($summaryTimestamp > $now) {
            $this->bus->dispatch(
                new SendNotificationMessage($event->getId(), 'summary'),
                [new DelayStamp(($summaryTimestamp - $now) * 1000)]
            );
        }
    }
}