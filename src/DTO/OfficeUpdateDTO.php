<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class OfficeUpdateDTO
{
    public function __construct(

        #[Assert\Length(max: 255)]
        public ?string $name = null,

        #[Assert\Length(max: 255)]
        public ?string $city = null,

        #[Assert\Range(notInRangeMessage: "Time zone should be in the range from -12 to 14", min: -12, max: 14)]
        public ?int $timeZone = null,

        public ?int $organizationId = null,
    ) {}
}