<?php

namespace App\DTO;

use App\Enum\Status;
use Nelmio\ApiDocBundle\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

readonly class MeetingRoomUpdateDTO
{
    public function __construct
    (
        #[Assert\Length(min: 2, max: 255)]
        public ?string $name = null,

        #[Assert\Length(min: 2, max: 512)]
        public ?string $description = null,

        #[Assert\Type('int')]
        public ?int $calendarCode = null,

        #[Assert\All([
            new Assert\File(
                maxSize: '5M',
                mimeTypes: ['image/jpeg', 'image/png', 'image/webp']
            )
        ])]
        public array $photos = [],

        #[Assert\Type('int')]
        #[Assert\Range(notInRangeMessage: "Uncorrected size", min: 1, max: 100)]
        public ?int $size = null,

        #[Assert\Choice(callback: [Status::class, 'getValidValues'], message: 'Invalid status value')]
        public ?string $status = null,

        #[Assert\Type('int')]
        public ?int $officeId = null,

        #[Assert\Type('boolean')]
        public ?bool $isPublic = null,

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