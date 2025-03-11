<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class EmployeeUpdateDTO
{
    public function __construct(

        #[Assert\Type('int')]
        public ?int $organizationId = null,

        #[Assert\Length(min: 2, max: 255)]
        public ?string $name = null,

        #[Assert\Length(min: 2, max: 255)]
        public ?string $surname = null,

        #[Assert\Length(min: 2, max: 255)]
        public ?string $patronymic = null,

        #[Assert\Email]
        public ?string $email = null,

        #[Assert\Length(min: 6, max: 255)]
        public ?string $password = null,
    ) {}
}