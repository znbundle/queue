<?php

namespace ZnBundle\Queue\Domain\Interfaces\Repositories;

use Illuminate\Support\Collection;
use ZnBundle\Queue\Domain\Entities\ScheduleEntity;
use ZnCore\Base\Libs\Repository\Interfaces\CrudRepositoryInterface;
use ZnCore\Base\Libs\Query\Entities\Query;

interface ScheduleRepositoryInterface extends CrudRepositoryInterface
{

    /**
     * @param Query|null $query
     * @return Collection | ScheduleEntity[]
     */
    public function allByChannel(string $channel = null, Query $query = null): Collection;
}
