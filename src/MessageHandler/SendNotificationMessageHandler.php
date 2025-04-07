<?php

namespace App\MessageHandler;

use App\Entity\Event;
use App\Message\SendNotificationMessage;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendNotificationMessageHandler
{
    private EntityManagerInterface $em;
    private NotificationService $notificationService;

    public function __construct(EntityManagerInterface $em, NotificationService $notificationService)
    {
        $this->em = $em;
        $this->notificationService = $notificationService;
    }

    public function __invoke(SendNotificationMessage $message): void
    {
        $event = $this->em->getRepository(Event::class)->find($message->getEventId());
        if (!$event) {
            return;
        }

        foreach ($event->getEmployees() as $employee) {
            if ($message->getType() === 'reminder') {
                $this->notificationService->sendMeetingReminder($employee, $event, $message->getMinutesBefore());
            } elseif ($message->getType() === 'summary') {
                $this->notificationService->sendMeetingSummary($employee, $event);
            }
        }
    }
}