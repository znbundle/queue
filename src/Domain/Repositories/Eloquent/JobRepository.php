<?php

namespace ZnBundle\Queue\Domain\Repositories\Eloquent;

use Illuminate\Support\Collection;
use ZnBundle\Queue\Domain\Entities\JobEntity;
use ZnBundle\Queue\Domain\Interfaces\Repositories\JobRepositoryInterface;
use ZnBundle\Queue\Domain\Queries\NewTaskQuery;
use ZnLib\Db\Base\BaseEloquentCrudRepository;

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
