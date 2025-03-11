<?php

namespace App\Interface;

use App\DTO\EmployeeCreateDTO;
use App\DTO\EmployeeUpdateDTO;
use App\Entity\Employee;

interface EmployeeServiceInterface
{
    public function getAllEmployees(): array;
    public function getEmployeeById(int $id): ?Employee;
    public function createEmployee(EmployeeCreateDTO $employeeCreateDTO): Employee;
    public function updateEmployee(int $id, EmployeeUpdateDTO $employeeUpdateDTO): Employee;
    public function deleteEmployee(int $id): void;
}