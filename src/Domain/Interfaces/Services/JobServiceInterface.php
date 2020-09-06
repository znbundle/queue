<?php

namespace PhpBundle\Queue\Domain\Interfaces\Services;

use PhpBundle\Queue\Domain\Entities\TotalEntity;
use PhpBundle\Queue\Domain\Enums\PriorityEnum;
use PhpBundle\Queue\Domain\Interfaces\JobInterface;

interface JobServiceInterface
{

    public function push(JobInterface $job, int $priority = PriorityEnum::NORMAL, string $channel = null);

    public function runAll(string $channel = null): TotalEntity;

}