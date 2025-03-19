<?php

namespace App\EventListener;

use App\Entity\Event;
use App\Service\YandexCalendarService;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;

class EventSynchronizationListener
{
    private YandexCalendarService $calendarService;
    public function __construct(YandexCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Event) {
            $this->calendarService->syncEvent($entity);
        }
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Event) {
            $this->calendarService->syncEvent($entity);
        }
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Event) {
            $this->calendarService->deleteEvent($entity);
        }
    }
}