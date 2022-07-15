<?php

namespace ZnBundle\Queue\Domain\Interfaces\Repositories;

use ZnBundle\Queue\Domain\Entities\JobEntity;
use ZnDomain\Query\Entities\Query;
use ZnDomain\Repository\Interfaces\CrudRepositoryInterface;

interface JobRepositoryInterface extends CrudRepositoryInterface
{

    /**
     * Выбрать невыполненные и зависшие задачи
     * @param Query|null $query
     * @return JobEntity[]
     */
    //public function newTasks(string $channel = null): Enumerable;
}