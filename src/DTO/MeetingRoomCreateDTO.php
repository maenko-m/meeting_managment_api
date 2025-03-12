<?php

namespace App\DTO;

use App\Enum\Status;
use Nelmio\ApiDocBundle\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

readonly class MeetingRoomCreateDTO
{
    public function __construct
    (
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 255)]
        public ?string $name,

        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 512)]
        public ?string $description,

        #[Assert\NotBlank]
        #[Assert\Type('int')]
        public ?int $calendarCode,

        #[Assert\NotBlank]
        public ?string $photoPath,

        #[Assert\NotBlank]
        #[Assert\Type('int')]
        #[Assert\Range(notInRangeMessage: "Uncorrected size", min: 1, max: 100)]
        public ?int $size,

        #[Assert\NotBlank]
        #[Assert\Choice(callback: [Status::class, 'getValidValues'], message: 'Invalid status value')]
        public ?string $status,

        #[Assert\NotBlank]
        #[Assert\Type('int')]
        public ?int $officeId,

        #[Assert\Type('boolean')]
        public bool $isPublic = true,

        #[Assert\All([
            new Assert\Type('int')
        ])]
        public array $employeeIds = [],
    ) {}

    #[Ignore]
    public function getStatusEnum(): ?Status
    {
        return Status::tryFrom($this->status);
    }
}