<?php

namespace ZnBundle\Queue\Domain\Interfaces\Repositories;

use ZnBundle\Queue\Domain\Entities\JobEntity;
use ZnCore\Domain\Query\Entities\Query;
use ZnCore\Domain\Repository\Interfaces\CrudRepositoryInterface;

interface JobRepositoryInterface extends CrudRepositoryInterface
{

    /**
     * Выбрать невыполненные и зависшие задачи
     * @param Query|null $query
     * @return JobEntity[]
     */
    //public function newTasks(string $channel = null): Enumerable;
}