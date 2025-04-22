<?php

namespace App\Message;

use DateTimeInterface;

class SendNotificationMessage
{
    public int $eventId;

    public string $type;

    public function __construct(int $eventId, string $type)
    {
        $this->eventId = $eventId;
        $this->type = $type;
    }
}