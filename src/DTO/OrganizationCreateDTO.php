<?php

namespace App\DTO;

use App\Enum\Status;
use Nelmio\ApiDocBundle\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

readonly class OrganizationCreateDTO
{
    public function __construct
    (
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 255)]
        public ?string $name,

        #[Assert\NotBlank]
        #[Assert\Choice(callback: [Status::class, 'getValidValues'], message: 'Invalid status value')]
        public ?string $status
    ) {}

    #[Ignore]
    public function getStatusEnum(): ?Status
    {
        return Status::tryFrom($this->status);
    }
}