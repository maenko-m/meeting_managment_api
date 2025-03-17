<?php

namespace App\Service;

use App\Entity\Employee;
use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class EventAccessChecker
{
    static function checkAccess(Event $event, UserInterface $user): bool
    {
        if ($event->getAuthor()->getEmail() === $user->getUserIdentifier()) return true;
        return false;
    }
}