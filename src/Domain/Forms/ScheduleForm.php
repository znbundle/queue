<?php

namespace ZnBundle\Queue\Domain\Forms;

use ZnCore\Domain\Interfaces\Entity\ValidateEntityByMetadataInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use ZnCore\Domain\Constraints\Arr;
use ZnCore\Base\Enums\StatusEnum;
use ZnCore\Domain\Constraints\Enum;

class ScheduleForm implements ValidateEntityByMetadataInterface
{

    protected $channel = null;

    protected $class = null;

    protected $data = null;

    protected $statusId = null;

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('channel', new Assert\NotBlank());
        $metadata->addPropertyConstraint('class', new Assert\NotBlank());
        $metadata->addPropertyConstraint('data', new Assert\NotBlank());
        $metadata->addPropertyConstraint('data', new Arr());
        $metadata->addPropertyConstraint('statusId', new Assert\NotBlank());
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

