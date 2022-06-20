<?php

namespace ZnBundle\Queue\Domain\Filters;

use ZnCore\Base\Libs\Validation\Interfaces\ValidationByMetadataInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use ZnCore\Domain\Constraints\Arr;
use ZnCore\Base\Enums\StatusEnum;
use ZnCore\Domain\Constraints\Enum;

class ScheduleFilter implements ValidationByMetadataInterface
{

    protected $channel = null;

    protected $class = null;

    protected $data = null;

    protected $statusId = null;

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('data', new Arr());
        $metadata->addPropertyConstraint('statusId', new Assert\Positive());
        $metadata->addPropertyConstraint('statusId', new Enum([
            'class' => StatusEnum::class,
        ]));
    }

    public function setChannel($value) : void
    {
        $this->channel = $value;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function setClass($value) : void
    {
        $this->class = $value;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setData($value) : void
    {
        $this->data = $value;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setStatusId($value) : void
    {
        $this->statusId = $value;
    }

    public function getStatusId()
    {
        return $this->statusId;
    }


}

