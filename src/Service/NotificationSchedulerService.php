<?php

namespace App\Service;

use App\Entity\Event;
use App\Message\SendNotificationMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Doctrine\DBAL\Connection;

class NotificationSchedulerService
{
    public function __construct(
        private MessageBusInterface $bus,
        private Connection $connection
    ) {}

    public function scheduleNotifications(Event $event): void
    {
        $this->clearChildRecurringEvents($event->getId()); //чистка мероприятий-клонов для уведомлений
        $this->clearEventQueue($event->getId()); //чиста очереди для уведолмлений

// Получаем дату и время начала события
        $date = $event->getDate();
        $timeStart = $event->getTimeStart();
        $timeEnd = $event->getTimeEnd();

// Создаем объект DateTime для времени начала события
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

// Создаем объект DateTime для времени окончания события
        $end = clone $start;
        $end->setTime(
            (int)$timeEnd->format('H'),
            (int)$timeEnd->format('i'),
            (int)$timeEnd->format('s')
        );

// Получаем текущее время в формате timestamp
        $now = time();

// Отправка напоминания за 60 минут до начала
        $reminderTimestamp = $start->getTimestamp() - 60 * 60; // 60 минут до начала
        if ($reminderTimestamp > $now) {
            $this->bus->dispatch(
                new SendNotificationMessage($event->getId(), 'reminder'),
                [new DelayStamp(($reminderTimestamp - $now) * 1000)] // Задержка в миллисекундах
            );
        }
// Отправка напоминания за 10 минут до начала
        $reminderTimestamp = $start->getTimestamp() - 10 * 60; // 60 минут до начала
        if ($reminderTimestamp > $now) {
            $this->bus->dispatch(
                new SendNotificationMessage($event->getId(), 'reminder', 10),
                [new DelayStamp(($reminderTimestamp - $now) * 1000)] // Задержка в миллисекундах
            );
        }

// Отправка уведомления после окончания
        $summaryTimestamp = $end->getTimestamp(); // Время окончания события
        if ($summaryTimestamp > $now) {
            $this->bus->dispatch(
                new SendNotificationMessage($event->getId(), 'summary'),
                [new DelayStamp(($summaryTimestamp - $now) * 1000)] // Задержка в миллисекундах
            );
        }
    }

    public function clearEventQueue(int $eventId): void
    {
        $this->connection->executeStatement(
            'DELETE FROM messenger_messages WHERE body LIKE :pattern',
            ['pattern' => '%"eventId":' . $eventId . '%']
        );
    }

    private function clearChildRecurringEvents(int $eventId): void
    {
        $this->connection->executeStatement(
            'DELETE FROM event WHERE recurrence_parent_id = :parentId',
            ['parentId' => $eventId]
        );
    }
}