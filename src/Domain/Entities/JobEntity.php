<?php

namespace ZnBundle\Queue\Domain\Entities;

use DateTime;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use ZnBundle\Queue\Domain\Enums\PriorityEnum;
use ZnBundle\Queue\Domain\Helpers\JobHelper;
use ZnBundle\Queue\Domain\Interfaces\JobInterface;
use ZnCore\Domain\Entity\Interfaces\EntityIdInterface;
use ZnCore\Domain\Entity\Interfaces\UniqueInterface;
use ZnCore\Base\Libs\Validation\Interfaces\ValidationByMetadataInterface;
use ZnCore\Base\Libs\Status\Enums\StatusEnum;

class JobEntity implements ValidationByMetadataInterface, EntityIdInterface, UniqueInterface
{

    private $id;
    private $channel;
    private $class;
    private $data;
    private $job;
    private $priority = PriorityEnum::NORMAL;
    private $delay = 0;
    private $attempt = 0;
    private $status = StatusEnum::ENABLED;
    private $pushedAt;
    private $reservedAt;
    private $doneAt;

    public function __construct()
    {
        $this->pushedAt = new DateTime;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {

    }

    public function unique(): array
    {
        return [];
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function setChannel($channel): void
    {
        $this->channel = $channel;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class): void
    {
        $this->class = $class;
    }

    public function getJob(): array
    {
        if( ! $this->getData()) {
            return [];
        }
        return JobHelper::decode($this->getData());
    }

    public function setJob(JobInterface $job): void
    {
        $base64Data = JobHelper::encode($job);
        $this->setData($base64Data);
        $this->setClass(get_class($job));
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data): void
    {
        $this->data = $data;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setPriority($priority): void
    {
        $this->priority = $priority;
    }

    public function getDelay()
    {
        return $this->delay;
    }

    public function setDelay($delay): void
    {
        $this->delay = $delay;
    }

    public function getAttempt()
    {
        return $this->attempt;
    }

    public function setAttempt($attempt): void
    {
        $this->attempt = $attempt;
    }

    public function incrementAttempt($step = 1): void
    {
        $this->attempt = $this->attempt + $step;
    }

    public function setCompleted(): void
    {
        $this->setReservedAt();
        $this->setDoneAt();
        $this->setStatus(StatusEnum::COMPLETED);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getPushedAt(): DateTime
    {
        return $this->pushedAt;
    }

    public function setPushedAt($pushedAt): void
    {
        if ($pushedAt instanceof DateTime) {
            $this->pushedAt = $pushedAt;
        } else {
            $this->pushedAt = new DateTime($pushedAt);
        }
    }

    public function getReservedAt(): ?DateTime
    {
        return $this->reservedAt;
    }

    public function setReservedAt($reservedAt = null): void
    {
        if ($reservedAt instanceof DateTime) {
            $this->reservedAt = $reservedAt;
        } else {
            $this->reservedAt = new DateTime($reservedAt);
        }
    }

    public function getDoneAt(): ?DateTime
    {
        return $this->doneAt;
    }

    public function setDoneAt($doneAt = null): void
    {
        if ($doneAt instanceof DateTime) {
            $this->doneAt = $doneAt;
        } else {
            $this->doneAt = new DateTime($doneAt);
        }
    }

}
