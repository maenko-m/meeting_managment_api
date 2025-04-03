<?php

namespace App\Repository;

use App\Entity\Employee;
use App\Entity\MeetingRoom;
use App\Enum\Status;
use App\Service\MeetingRoomAccessChecker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MeetingRoom>
 */
class MeetingRoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MeetingRoom::class);
    }

    public function getAllByFilter(?string $name = null, ?int $office_id = null, bool $isActive = false, bool $canAccess = false, ?Employee $user = null, int $page = 1, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('mr');

        if ($office_id) {
            $qb->andWhere('mr.office = :office')
                ->setParameter('office', $office_id);
        }

        if ($name) {
            $qb->andWhere('mr.name LIKE :name')
                ->setParameter('name', '%'.$name.'%');
        }

        if ($isActive) {
            $qb->andWhere('mr.status = :status')
                ->setParameter('status', Status::ACTIVE);
        }

        if ($canAccess) {
            $qb->leftJoin('mr.employees', 'e')
                ->andWhere('e.id = :userId OR mr.is_public = true')
                ->setParameter('userId', $user->getId());
        }

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $data = $qb->getQuery()->getResult();

        foreach ($data as $room) {
            $room->setAccess(MeetingRoomAccessChecker::canAccess($room, $user));
        }

        return ['data' => $data, 'total' => [$this->count()]];
    }

    public function findWithAccess(int $meetingRoomId, ?Employee $user = null): ?MeetingRoom
    {
        $room = $this->find($meetingRoomId);

        if (!$room) {
            return null;
        }

        $room->setAccess(MeetingRoomAccessChecker::canAccess($room, $user));

        return $room;
    }
}
