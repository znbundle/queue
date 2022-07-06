<?php

namespace ZnBundle\Queue\Domain\Interfaces\Repositories;

use ZnBundle\Queue\Domain\Entities\ScheduleEntity;
use ZnCore\Collection\Interfaces\Enumerable;
use ZnCore\Domain\Query\Entities\Query;
use ZnCore\Domain\Repository\Interfaces\CrudRepositoryInterface;

interface ScheduleRepositoryInterface extends CrudRepositoryInterface
{

    /**
     * @param Query|null $query
     * @return Enumerable | ScheduleEntity[]
     */
    public function allByChannel(string $channel = null, Query $query = null): Enumerable;
}
