<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class EventCreateDTO
{
    public function __construct(

        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 255)]
        public ?string $name,

        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 255)]
        public ?string $description,

        #[Assert\NotBlank]
        #[Assert\Date]
        public ?string $date,

        #[Assert\NotBlank]
        #[Assert\Time]
        public ?string $timeStart,

        #[Assert\NotBlank]
        #[Assert\Time]
        public ?string $timeEnd,

        #[Assert\NotBlank]
        #[Assert\Type('int')]
        public ?int    $authorId,

        #[Assert\NotBlank]
        #[Assert\Type('int')]
        public ?int    $meetingRoomId,

        #[Assert\All([
            new Assert\Type('int')
        ])]
        public array   $employeeIds = []
    ) {}
}
