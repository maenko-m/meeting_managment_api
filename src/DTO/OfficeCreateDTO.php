<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class OfficeCreateDTO
{
    public function __construct(

        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public ?string $name,

        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public ?string $city,

        #[Assert\NotBlank]
        #[Assert\Range(notInRangeMessage: "Time zone should be in the range from -12 to 14", min: -12, max: 14)]
        public ?int $timeZone,

        #[Assert\NotBlank]
        public ?int $organizationId,
    ) {}
}