<?php

namespace App\Service;

use App\DTO\MeetingRoomCreateDTO;
use App\DTO\MeetingRoomUpdateDTO;
use App\Entity\Employee;
use App\Entity\MeetingRoom;
use App\Entity\Office;
use App\Interface\MeetingRoomServiceInterface;
use App\Repository\MeetingRoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class MeetingRoomService implements MeetingRoomServiceInterface
{
    private EntityManagerInterface $em;
    private MeetingRoomRepository $meetingRoomRepository;


    public function __construct(EntityManagerInterface $em, MeetingRoomRepository $meetingRoomRepository)
    {
        $this->em = $em;
        $this->meetingRoomRepository = $meetingRoomRepository;
    }

    public function getAllMeetingRooms(array $filters, ?UserInterface $user): array
    {
        $office_id = $filters['office_id'] ?? null;
        $name = $filters['name'] ?? null;
        $isActive = $filters['is_active'] ?? false;
        $canAccess = $filters['can_access'] ?? false;
        $page = $filters['page'] ?? 1;
        $limit = $filters['limit'] ?? 10;

        return $this->meetingRoomRepository->getAllByFilter($name, $office_id, $isActive, $canAccess, $user, $page, $limit);
    }

    public function getMeetingRoomById(int $id, ?UserInterface $user): ?MeetingRoom
    {
        return $this->meetingRoomRepository->findWithAccess($id, $user);
    }

    public function createMeetingRoom(MeetingRoomCreateDTO $dto): MeetingRoom
    {
        $office = $this->em->getRepository(Office::class)->find($dto->officeId);

        if (!$office) {
            throw new NotFoundHttpException('Office not found');
        }

        $meetingRoom = (new MeetingRoom())
            ->setName($dto->name)
            ->setDescription($dto->description)
            ->setCalendarCode($dto->calendarCode)
            ->setPhotoPath($dto->photoPath)
            ->setSize($dto->size)
            ->setStatus($dto->getStatusEnum())
            ->setOffice($office)
            ->setIsPublic($dto->isPublic)
        ;

        foreach ($dto->employeeIds as $employeeId) {
            $employee = $this->em->getRepository(Employee::class)->find($employeeId);
            if (!$employee) {
                throw new NotFoundHttpException('Employee not found');
            }
            $meetingRoom->addEmployee($employee);
        }

        $this->em->persist($meetingRoom);
        $this->em->flush();

        return $meetingRoom;
    }

    public function updateMeetingRoom(int $id, MeetingRoomUpdateDTO $dto): MeetingRoom
    {
        $meetingRoom = $this->em->getRepository(MeetingRoom::class)->find($id);

        if (!$meetingRoom) {
            throw new NotFoundHttpException('Meeting room not found');
        }

        if ($dto->name) {
            $meetingRoom->setName($dto->name);
        }

        if ($dto->description) {
            $meetingRoom->setDescription($dto->description);
        }

        if ($dto->calendarCode) {
            $meetingRoom->setCalendarCode($dto->calendarCode);
        }

        if ($dto->photoPath) {
            $meetingRoom->setPhotoPath($dto->photoPath);
        }

        if ($dto->size) {
            $meetingRoom->setSize($dto->size);
        }

        if ($dto->status) {
            $meetingRoom->setStatus($dto->getStatusEnum());
        }

        if ($dto->officeId) {
            $office = $this->em->getRepository(Office::class)->find($dto->officeId);
            if (!$office) {
                throw new NotFoundHttpException('Office not found');
            }
            $meetingRoom->setOffice($office);
        }

        if ($dto->isPublic) {
            $meetingRoom->setIsPublic($dto->isPublic);
        }

        if ($dto->employeeIds) {
            $meetingRoom->clearEmployees();
            foreach ($dto->employeeIds as $employeeId) {
                $employee = $this->em->getRepository(Employee::class)->find($employeeId);
                if (!$employee) {
                    throw new NotFoundHttpException('Employee not found');
                }
                $meetingRoom->addEmployee($employee);
            }
        }

        $this->em->flush();

        return $meetingRoom;
    }

    public function deleteMeetingRoom(int $id): void
    {
        $meetingRoom = $this->em->getRepository(MeetingRoom::class)->find($id);

        if (!$meetingRoom) {
            throw new NotFoundHttpException('Meeting room not found');
        }

        $this->em->remove($meetingRoom);
        $this->em->flush();
    }
}