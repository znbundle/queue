<?php

namespace ZnBundle\Queue\Domain\Entities;

use ZnCore\Entity\Interfaces\EntityIdInterface;
use DateTime;
use ZnCore\Validation\Interfaces\ValidationByMetadataInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use ZnCore\Base\Arr\Constraints\Arr;
use ZnLib\Components\Status\Enums\StatusEnum;
use ZnCore\Base\Enum\Constraints\Enum;
use ZnCore\Entity\Interfaces\UniqueInterface;

class ScheduleEntity implements EntityIdInterface, ValidationByMetadataInterface, UniqueInterface
{

    protected $id = null;

    protected $channel = null;
    
    protected $expression = null;

    protected $class = null;

    protected $data = null;

    protected $statusId = null;

    protected $executedAt = null;

    protected $createdAt = null;

    protected $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('id', new Assert\Positive());
        $metadata->addPropertyConstraint('channel', new Assert\NotBlank());
        $metadata->addPropertyConstraint('class', new Assert\NotBlank());
//        $metadata->addPropertyConstraint('data', new Assert\NotBlank());
//        $metadata->addPropertyConstraint('data', new Arr());
        $metadata->addPropertyConstraint('statusId', new Assert\NotBlank());
        $metadata->addPropertyConstraint('statusId', new Assert\Positive());
        $metadata->addPropertyConstraint('statusId', new Enum([
            'class' => StatusEnum::class,
        ]));
        $metadata->addPropertyConstraint('createdAt', new Assert\NotBlank());
//        $metadata->addPropertyConstraint('updatedAt', new Assert\NotBlank());
    }

    public function unique() : array
    {
        return [];
    }

    public function setId($value) : void
    {
        $this->id = $value;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setChannel($value) : void
    {
        $this->channel = $value;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function getExpression()
    {
        return $this->expression;
    }

    public function setExpression($expression): void
    {
        $this->expression = $expression;
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

    public function getExecutedAt()
    {
        return $this->executedAt;
    }

    public function setExecutedAt($executedAt): void
    {
        $this->executedAt = $executedAt;
    }

    public function setCreatedAt($value) : void
    {
        $this->createdAt = $value;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setUpdatedAt($value) : void
    {
        $this->updatedAt = $value;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
