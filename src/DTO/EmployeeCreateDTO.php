<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class EmployeeCreateDTO
{
    public function __construct(

        #[Assert\NotBlank]
        #[Assert\Type('int')]
        public ?int $organizationId,

        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 255)]
        public ?string $name,

        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 255)]
        public ?string $surname,

        #[Assert\Length(min: 2, max: 255)]
        public ?string $patronymic,

        #[Assert\NotBlank]
        #[Assert\Email]
        public ?string $email,

        #[Assert\NotBlank]
        #[Assert\Length(min: 6, max: 255)]
        public ?string $password,
    ) {}
}