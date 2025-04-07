<?php

namespace App\Message;

class SendNotificationMessage
{
    private int $eventId;
    private string $type;
    private int $minutesBefore;

    public function __construct(int $eventId, string $type, int $minutesBefore = 0)
    {
        $this->eventId = $eventId;
        $this->type = $type;
        $this->minutesBefore = $minutesBefore;
    }

    public function getEventId(): int
    {
        return $this->eventId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMinutesBefore(): int
    {
        return $this->minutesBefore;
    }
}