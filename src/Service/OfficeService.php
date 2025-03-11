<?php

namespace App\Service;

use App\DTO\OfficeCreateDTO;
use App\DTO\OfficeUpdateDTO;
use App\Entity\Office;
use App\Entity\Organization;
use App\Interface\OfficeServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OfficeService implements OfficeServiceInterface
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getAllOffices(): array
    {
        return $this->em->getRepository(Office::class)->findAll();
    }

    public function getOfficeById(int $id): ?Office
    {
        return $this->em->getRepository(Office::class)->find($id);
    }

    public function createOffice(OfficeCreateDTO $dto): Office
    {
        $organization = $this->em->getRepository(Organization::class)->find($dto->organizationId);

        if (!$organization) {
            throw new NotFoundHttpException('Organization not found');
        }

        $office = (new Office())
            ->setName($dto->name)
            ->setCity($dto->city)
            ->setTimeZone($dto->timeZone)
            ->setOrganization($organization);

        $this->em->persist($office);
        $this->em->flush();

        return $office;
    }

    public function updateOffice(int $id, OfficeUpdateDTO $dto): Office
    {
        $office = $this->getOfficeById($id);

        if (!$office) {
            throw new NotFoundHttpException('Office not found');
        }

        if ($dto->name) {
            $office->setName($dto->name);
        }

        if ($dto->city) {
            $office->setCity($dto->city);
        }

        if ($dto->timeZone !== null) {
            $office->setTimeZone($dto->timeZone);
        }

        if ($dto->organizationId) {
            $organization = $this->em->getRepository(Organization::class)->find($dto->organizationId);
            if (!$organization) {
                throw new NotFoundHttpException('Organization not found');
            }
            $office->setOrganization($organization);
        }

        $this->em->flush();

        return $office;
    }

    public function deleteOffice(int $id): void
    {
        $office = $this->getOfficeById($id);

        if (!$office) {
            throw new NotFoundHttpException('Office not found');
        }

        $this->em->remove($office);
        $this->em->flush();
    }
}