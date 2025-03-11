<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class EventUpdateDTO
{
    public function __construct(

        #[Assert\Length(min: 2, max: 255)]
        public ?string $name = null,

        #[Assert\Length(min: 2, max: 255)]
        public ?string $description = null,

        #[Assert\Date]
        public ?string $date = null,

        #[Assert\Time]
        public ?string $timeStart  = null,

        #[Assert\Time]
        public ?string $timeEnd  = null,

        #[Assert\Type('int')]
        public ?int    $authorId = null,

        #[Assert\Type('int')]
        public ?int    $meetingRoomId = null,

        #[Assert\All([
            new Assert\Type('int')
        ])]
        public array   $employeeIds = []
    ) {}
}
