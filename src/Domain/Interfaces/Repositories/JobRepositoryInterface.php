<?php

namespace ZnBundle\Queue\Domain\Interfaces\Repositories;

use Illuminate\Support\Collection;
use ZnBundle\Queue\Domain\Entities\JobEntity;
use ZnCore\Base\Libs\Repository\Interfaces\CrudRepositoryInterface;
use ZnCore\Base\Libs\Query\Entities\Query;

interface JobRepositoryInterface extends CrudRepositoryInterface
{

    /**
     * Выбрать невыполненные и зависшие задачи
     * @param Query|null $query
     * @return JobEntity[]
     */
    //public function newTasks(string $channel = null): Collection;
}