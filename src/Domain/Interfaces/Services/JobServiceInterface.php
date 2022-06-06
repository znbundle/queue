<?php

namespace ZnBundle\Queue\Domain\Interfaces\Services;

use ZnBundle\Queue\Domain\Entities\TotalEntity;
use ZnBundle\Queue\Domain\Enums\PriorityEnum;
use ZnBundle\Queue\Domain\Interfaces\JobInterface;

interface JobServiceInterface
{

    public function push(JobInterface $job, int $priority = PriorityEnum::NORMAL, string $channel = null);

    public function runAll(string $channel = null): TotalEntity;

    public function touch(): void;
}