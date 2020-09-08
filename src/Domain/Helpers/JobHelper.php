<?php

namespace ZnBundle\Queue\Domain\Helpers;

class JobHelper
{

    public static function encode(object $job): string
    {
        $data = get_object_vars($job);
        $serializedData = serialize($data);
        $base64Data = base64_encode($serializedData);
        return $base64Data;
    }

    public static function decode(string $data)
    {
        $serializedData = base64_decode($data);
        $job = unserialize($serializedData);
        return $job;
    }

}