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

        $qb->leftJoin('mr.office', 'o');

        $qb->andWhere('o.organization = :organizationId')
            ->setParameter('organizationId', $user->getOrganization()->getId());

        if ($office_id) {
            $qb->andWhere('mr.office = :office')
                ->setParameter('office', $office_id);
        }

        if ($name) {
            $qb->andWhere('mr.name LIKE :name')
                ->setParameter('name', '%'.$name.'%');
        }

        $qb->andWhere('mr.status = :status')
            ->setParameter('status', Status::ACTIVE);

        $qb->leftJoin('mr.employees', 'e')
            ->andWhere('e.id = :userId OR mr.is_public = true')
            ->setParameter('userId', $user->getId());

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $data = $qb->getQuery()->getResult();

        $countQb = clone $qb;
        $total = $countQb->select('COUNT(mr.id) as total')
            ->setFirstResult(null)
            ->setMaxResults(null)
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'data' => $data,
            'meta' => [
                'total' => (int) $total,
                'page' => $page,
                'limit' => $limit,
                'totalPages' => (int) ceil($total / $limit)
            ]
        ];
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
