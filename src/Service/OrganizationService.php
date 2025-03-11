<?php

namespace App\Service;

use App\DTO\OrganizationCreateDTO;
use App\DTO\OrganizationUpdateDTO;
use App\Entity\Organization;
use App\Interface\OrganizationServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrganizationService implements OrganizationServiceInterface
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getAllOrganizations(): array
    {
        return $this->em->getRepository(Organization::class)->findAll();
    }

    public function getOrganizationById(int $id): ?Organization
    {
        return $this->em->getRepository(Organization::class)->find($id);
    }

    public function createOrganization(OrganizationCreateDTO $dto): Organization
    {
        $organization = (new Organization())
            ->setName($dto->name)
            ->setStatus($dto->getStatusEnum());

        $this->em->persist($organization);
        $this->em->flush();

        return $organization;
    }

    public function updateOrganization(int $id, OrganizationUpdateDTO $dto): Organization
    {
        $organization = $this->getOrganizationById($id);

        if (!$organization) {
            throw new NotFoundHttpException('Organization not found');
        }

        if ($dto->name) {
            $organization->setName($dto->name);
        }

        if ($dto->status) {
            $organization->setStatus($dto->getStatusEnum());
        }

        $this->em->flush();

        return $organization;
    }

    public function deleteOrganization(int $id): void
    {
        $organization = $this->getOrganizationById($id);

        if (!$organization) {
            throw new NotFoundHttpException('Organization not found');
        }

        $this->em->remove($organization);
        $this->em->flush();
    }
}