<?php

namespace PhpBundle\Queue\Domain\Repositories\Eloquent;

use Illuminate\Support\Collection;
use PhpBundle\Queue\Domain\Entities\JobEntity;
use PhpBundle\Queue\Domain\Interfaces\Repositories\JobRepositoryInterface;
use PhpBundle\Queue\Domain\Queries\NewTaskQuery;
use PhpLab\Eloquent\Db\Base\BaseEloquentCrudRepository;

class JobRepository extends BaseEloquentCrudRepository implements JobRepositoryInterface
{

    protected $tableName = 'queue_job';

    public function getEntityClass(): string
    {
        return JobEntity::class;
    }

    /*public function newTasks(string $channel = null): Collection
    {
        $query = new NewTaskQuery($channel);
        return $this->all($query);
    }*/
}
