<?php

namespace App\Interface;

use App\DTO\EventCreateDTO;
use App\DTO\EventUpdateDTO;
use App\Entity\Event;
use Symfony\Component\Security\Core\User\UserInterface;

interface EventServiceInterface {
    public function getAllEvents(array $filters, ?UserInterface $user): array;
    public function getEventById(int $eventId): ?Event;
    public function createEvent(EventCreateDTO $dto): Event;
    public function updateEvent(int $id, EventUpdateDTO $dto): Event;
    public function deleteEvent(int $eventId): void;
}