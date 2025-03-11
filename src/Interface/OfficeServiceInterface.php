<?php

namespace App\Interface;

use App\DTO\OfficeCreateDTO;
use App\DTO\OfficeUpdateDTO;
use App\Entity\Office;

interface OfficeServiceInterface
{
    public function getAllOffices(): array;
    public function getOfficeById(int $id): ?Office;
    public function createOffice(OfficeCreateDTO $dto): Office;
    public function updateOffice(int $id, OfficeUpdateDTO $dto): Office;
    public function deleteOffice(int $id): void;
}