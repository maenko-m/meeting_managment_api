<?php

namespace App\Serializer;

class DateCallback
{
    public function __invoke(null|string|\DateTimeInterface $object): null|string|\DateTimeInterface
    {
        if ($object === null) {
            return null;
        }

        if (!($object instanceof \DateTimeInterface)) {
            return $object;
        }

        return $object->format('Y-m-d');
    }
}