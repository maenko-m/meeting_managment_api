<?php

namespace App\Service;

use App\Entity\Employee;
use App\Entity\MeetingRoom;

class MeetingRoomAccessChecker
{
    public static function canAccess(MeetingRoom $room, Employee $employee): bool
    {
        return $room->getIsPublic() || $room->getEmployees()->contains($employee);
    }
}