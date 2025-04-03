<?php

namespace App\Repository;

use App\Entity\Employee;
use App\Entity\Event;
use App\Entity\MeetingRoom;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function getAllByFilter(?int $room_id = null, ?string $type = null, ?string $name = null, ?Employee $user = null, bool $descOrder = false, ?string $isArchive = null, ?string $date = null, ?int $office_id = null, int $page = 1, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('e');

        if ($room_id) {
            $qb->andWhere('e.meeting_room = :roomId')
                ->setParameter('roomId', $room_id);
        }

        if ($type) {
            if ($type === 'организатор') {
                $qb->andWhere('e.author = :user')
                    ->setParameter('user', $user);
            } elseif ($type === 'участник') {
                $qb->andWhere(':user MEMBER OF e.employees')
                    ->setParameter('user', $user);
            }
        }

        if ($name) {
            $qb->andWhere('e.name LIKE :name')
                ->setParameter('name', '%'.$name.'%');
        }

        if ($isArchive) {
            if ($isArchive === 'true') {
                $qb->andWhere('e.date < :dateToday');
            } elseif ($isArchive === 'false') {
                $qb->andWhere('e.date >= :dateToday');
            }
            $qb->setParameter('dateToday', (new \DateTime())->setTime(0, 0, 0));
        }

        if ($date) {
            $dateObj = new \DateTime($date);
            $qb->andWhere('e.date = :date')
                ->setParameter('date', $dateObj);
        }

        if ($office_id) {
            $qb->join('e.meeting_room', 'mr')
                ->join('mr.office', 'o')
                ->andWhere('o.id = :officeId')
                ->setParameter('officeId', $office_id);
        }

        if ($descOrder) {
            $qb->orderBy('e.date', 'DESC');
        } else {
            $qb->orderBy('e.date', 'ASC');
        }

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $data = $qb->getQuery()->getResult();

        $countForAuthor = $this->createQueryBuilder('e')
            ->select('COUNT(e.id) as total')
            ->andWhere('e.author = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        $countForMember = $this->createQueryBuilder('e')
            ->select('COUNT(e.id) as total')
            ->andWhere(':user MEMBER OF e.employees')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        return ["data" => $data, "total" => [$countForAuthor, $countForMember] ];
    }

    public function getAllByDate(\DateTimeInterface $date): array
    {
        $qb = $this->createQueryBuilder('e');

        $qb->andWhere('e.date = :date')
            ->setParameter('date', $date);

        return $qb->getQuery()->getResult();
    }

    public function getConflictingEvents(
        MeetingRoom $meetingRoom,
        \DateTimeInterface $date,
        \DateTimeInterface $timeStart,
        \DateTimeInterface $timeEnd,
        ?int $excludeEventId = null
    ): array {
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.meeting_room = :meetingRoom')
            ->andWhere('e.date = :date')
            ->andWhere('e.time_start < :timeEnd')
            ->andWhere('e.time_end > :timeStart')
            ->setParameter('meetingRoom', $meetingRoom)
            ->setParameter('date', $date)
            ->setParameter('timeStart', $timeStart)
            ->setParameter('timeEnd', $timeEnd);

        if ($excludeEventId) {
            $qb->andWhere('e.id != :excludeId')
                ->setParameter('excludeId', $excludeEventId);
        }

        return $qb->getQuery()->getResult();
    }
}
