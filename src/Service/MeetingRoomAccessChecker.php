<?php

namespace App\Service;

use App\Entity\Employee;
use App\Entity\MeetingRoom;

class MeetingRoomAccessChecker
{
    public function canAccess(MeetingRoom $room, Employee $employee): bool
    {
        return $room->getIsPublic() || $room->getEmployees()->contains($employee);
    }
}