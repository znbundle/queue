<?php

namespace ZnBundle\Queue\Domain\Interfaces\Services;

use ZnCore\Domain\Collection\Interfaces\Enumerable;
use ZnCore\Domain\Collection\Libs\Collection;
use ZnBundle\Queue\Domain\Entities\JobEntity;
use ZnBundle\Queue\Domain\Entities\ScheduleEntity;
use ZnBundle\Queue\Domain\Entities\TotalEntity;
use ZnCore\Domain\Service\Interfaces\CrudServiceInterface;
use ZnCore\Domain\Query\Entities\Query;

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
