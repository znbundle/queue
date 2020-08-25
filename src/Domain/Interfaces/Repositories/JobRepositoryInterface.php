<?php

namespace PhpBundle\Queue\Domain\Interfaces\Repositories;

use Illuminate\Support\Collection;
use PhpBundle\Queue\Domain\Entities\JobEntity;
use PhpLab\Core\Domain\Interfaces\Repository\CrudRepositoryInterface;
use PhpLab\Core\Domain\Libs\Query;

interface JobRepositoryInterface extends CrudRepositoryInterface
{

    /**
     * Выбрать невыполненные и зависшие задачи
     * @param Query|null $query
     * @return JobEntity[]
     */
    //public function newTasks(string $channel = null): Collection;
}