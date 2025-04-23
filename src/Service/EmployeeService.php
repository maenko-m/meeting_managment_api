<?php

namespace App\Service;

use App\DTO\EmployeeCreateDTO;
use App\DTO\EmployeeUpdateDTO;
use App\Entity\Employee;
use App\Entity\Organization;
use App\Interface\EmployeeServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class EmployeeService implements EmployeeServiceInterface
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    public function getAllEmployees(?Employee $user = null): array
    {
        $organization = $user->getOrganization();

        $organizationId = $organization->getId();

        return $this->em->getRepository(Employee::class)->findBy([
            'organization' => $organizationId
        ]);
    }

    public function getEmployeeById(int $id): ?Employee
    {
        return $this->em->getRepository(Employee::class)->find($id);
    }

    public function createEmployee(EmployeeCreateDTO $employeeCreateDTO): Employee
    {
        $organization = $this->em->getRepository(Organization::class)->find($employeeCreateDTO->organizationId);

        if (!$organization) {
            throw new NotFoundHttpException('Organization not found');
        }

        $employee = (new Employee())
            ->setName($employeeCreateDTO->name)
            ->setSurname($employeeCreateDTO->surname)
            ->setPatronymic($employeeCreateDTO->patronymic)
            ->setOrganization($organization)
            ->setEmail($employeeCreateDTO->email)
            ->setRoles(['ROLE_USER']);

        $hasPassword = $this->passwordHasher->hashPassword($employee, $employeeCreateDTO->password);
        $employee->setPassword($hasPassword);

        $this->em->persist($employee);
        $this->em->flush();

        return $employee;
    }

    public function updateEmployee(int $id, EmployeeUpdateDTO $employeeUpdateDTO): Employee
    {
        $employee = $this->getEmployeeById($id);

        if (!$employee) {
            throw new NotFoundHttpException('Employee not found');
        }

        if ($employeeUpdateDTO->name) {
            $employee->setName($employeeUpdateDTO->name);
        }

        if ($employeeUpdateDTO->surname) {
            $employee->setSurname($employeeUpdateDTO->surname);
        }

        if ($employeeUpdateDTO->patronymic) {
            $employee->setPatronymic($employeeUpdateDTO->patronymic);
        }

        if ($employeeUpdateDTO->email) {
            $employee->setEmail($employeeUpdateDTO->email);
        }

        if ($employeeUpdateDTO->password) {
            $hasPassword = $this->passwordHasher->hashPassword($employee, $employeeUpdateDTO->password);
            $employee->setPassword($hasPassword);
        }

        if ($employeeUpdateDTO->organizationId) {
            $organization = $this->em->getRepository(Organization::class)->find($employeeUpdateDTO->organizationId);
            if (!$organization) {
                throw new NotFoundHttpException('Organization not found');
            }
            $employee->setOrganization($organization);
        }

        $this->em->flush();

        return $employee;
    }

    public function deleteEmployee(int $id): void
    {
        $employee = $this->getEmployeeById($id);

        if (!$employee) {
            throw new NotFoundHttpException('Employee not found');
        }

        $this->em->remove($employee);
        $this->em->flush();
    }
}