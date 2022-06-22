<?php

namespace ZnBundle\Queue\Domain\Interfaces\Services;

use Illuminate\Support\Collection;
use ZnBundle\Queue\Domain\Entities\JobEntity;
use ZnBundle\Queue\Domain\Entities\ScheduleEntity;
use ZnBundle\Queue\Domain\Entities\TotalEntity;
use ZnCore\Domain\Service\Interfaces\CrudServiceInterface;
use ZnCore\Domain\Query\Entities\Query;

interface ScheduleServiceInterface extends CrudServiceInterface
{

    /**
     * @param string|null $channel
     * @return Collection | JobEntity[]
     */
    public function runAll(string $channel = null): Collection;

    /**
     * @param Query|null $query
     * @return Collection | ScheduleEntity[]
     */
    public function allByChannel(string $channel = null, Query $query = null): Collection;
}
