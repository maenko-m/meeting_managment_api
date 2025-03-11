<?php

namespace App\Interface;

use App\DTO\MeetingRoomCreateDTO;
use App\DTO\MeetingRoomUpdateDTO;
use App\Entity\MeetingRoom;
use Symfony\Component\Security\Core\User\UserInterface;

interface MeetingRoomServiceInterface
{
    public function getAllMeetingRooms(array $filters, ?UserInterface $user): array;
    public function getMeetingRoomById(int $id, ?UserInterface $user): ?MeetingRoom;
    public function createMeetingRoom(MeetingRoomCreateDTO $dto): MeetingRoom;
    public function updateMeetingRoom(int $id, MeetingRoomUpdateDTO $dto): MeetingRoom;
    public function deleteMeetingRoom(int $id): void;
}