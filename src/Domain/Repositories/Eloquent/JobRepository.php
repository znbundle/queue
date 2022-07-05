<?php

namespace ZnBundle\Queue\Domain\Repositories\Eloquent;

use ZnBundle\Queue\Domain\Entities\JobEntity;
use ZnBundle\Queue\Domain\Interfaces\Repositories\JobRepositoryInterface;
use ZnDatabase\Eloquent\Domain\Base\BaseEloquentCrudRepository;

class JobRepository extends BaseEloquentCrudRepository implements JobRepositoryInterface
{

    protected $tableName = 'queue_job';

    public function getEntityClass(): string
    {
        return JobEntity::class;
    }

    /*public function newTasks(string $channel = null): Enumerable
    {
        $query = new NewTaskQuery($channel);
        return $this->findAll($query);
    }*/
}
