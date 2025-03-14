<?php

namespace App\Repository;

use App\Entity\Employee;
use App\Entity\Event;
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

    public function getAllByFilter(?int $room_id = null, ?string $type = null, ?string $name = null, ?Employee $user = null, bool $descOrder = false, int $page = 1, int $limit = 10): array
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

        if ($descOrder) {
            $qb->orderBy('e.date', 'DESC');
        } else {
            $qb->orderBy('e.date', 'ASC');
        }

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function getAllByDate(\DateTimeInterface $date): array
    {
        $qb = $this->createQueryBuilder('e');

        $qb->andWhere('e.date = :date')
            ->setParameter('date', $date);

        return $qb->getQuery()->getResult();
    }
}
