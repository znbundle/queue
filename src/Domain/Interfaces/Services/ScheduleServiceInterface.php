<?php

namespace ZnBundle\Queue\Domain\Interfaces\Services;

use ZnBundle\Queue\Domain\Entities\JobEntity;
use ZnBundle\Queue\Domain\Entities\ScheduleEntity;
use ZnCore\Collection\Interfaces\Enumerable;
use ZnDomain\Query\Entities\Query;
use ZnDomain\Service\Interfaces\CrudServiceInterface;

interface ScheduleServiceInterface extends CrudServiceInterface
{

    /**
     * @param string|null $channel
     * @return Enumerable | JobEntity[]
     */
    public function runAll(string $channel = null): Enumerable;

    /**
     * @param Query|null $query
     * @return Enumerable | ScheduleEntity[]
     */
    public function allByChannel(string $channel = null, Query $query = null): Enumerable;
}
