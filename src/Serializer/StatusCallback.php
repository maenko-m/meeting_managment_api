<?php

namespace App\Serializer;

use App\Enum\Status;

class StatusCallback
{
    public function __invoke(null|string|Status $object): null|string|Status
    {
        if ($object === null) {
            return null;
        }

        if (!($object instanceof Status)) {
            return $object;
        }

        return $object->value;
    }
}