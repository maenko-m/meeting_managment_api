<?php

namespace App\EventListener;

use App\Entity\Office;
use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\Status;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Organization::class)]
class OrganizationStatusListener
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function preUpdate(Organization $organization, PreUpdateEventArgs $eventArgs): void
    {
        if ($eventArgs->hasChangedField('status') && $eventArgs->getNewValue('status') !== Status::ACTIVE) {
            $this->deactivateMeetingRooms($organization);
        }
    }

    private function deactivateMeetingRooms(Organization $organization): void
    {
        $offices = $this->em->getRepository(Office::class)->findBy(['organization' => $organization]);

        foreach ($offices as $office) {
            foreach ($office->getMeetingRooms() as $meetingRoom) {
                $meetingRoom->setStatus(Status::INACTIVE);
            }
        }
    }
}