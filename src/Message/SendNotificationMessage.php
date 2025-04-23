<?php

namespace App\Message;

use DateTimeInterface;

class SendNotificationMessage
{
    public int $eventId;

    public string $type;
    public int $minutesBefore;

    public function __construct(int $eventId, string $type, int $minutesBefore = 60)
    {
        $this->eventId = $eventId;
        $this->type = $type;
        $this->minutesBefore = $minutesBefore;
    }
}