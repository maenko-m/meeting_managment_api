<?php

namespace App\DTO;

use App\Enum\Status;
use Nelmio\ApiDocBundle\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

readonly class OrganizationUpdateDTO
{
    public function __construct
    (
        #[Assert\Length(min: 2, max: 255)]
        public ?string $name = null,

        #[Assert\Choice(callback: [Status::class, 'getValidValues'], message: 'Invalid status value')]
        public ?string $status = null,
    ) {}

    #[Ignore]
    public function getStatusEnum(): ?Status
    {
        return Status::tryFrom($this->status);
    }
}