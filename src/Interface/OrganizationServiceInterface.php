<?php

namespace App\Interface;

use App\DTO\OrganizationCreateDTO;
use App\DTO\OrganizationUpdateDTO;
use App\Entity\Organization;

interface OrganizationServiceInterface
{
    public function getAllOrganizations(): array;
    public function getOrganizationById(int $id): ?Organization;
    public function createOrganization(OrganizationCreateDTO $dto): Organization;
    public function updateOrganization(int $id, OrganizationUpdateDTO $dto): Organization;
    public function deleteOrganization(int $id): void;
}