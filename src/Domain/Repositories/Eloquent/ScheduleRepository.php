<?php

namespace ZnBundle\Queue\Domain\Repositories\Eloquent;

use ZnBundle\Queue\Domain\Entities\ScheduleEntity;
use ZnBundle\Queue\Domain\Interfaces\Repositories\ScheduleRepositoryInterface;
use ZnCore\Collection\Interfaces\Enumerable;
use ZnDomain\Query\Entities\Query;
use ZnDomain\Repository\Mappers\TimeMapper;
use ZnDatabase\Eloquent\Domain\Base\BaseEloquentCrudRepository;

class ScheduleRepository extends BaseEloquentCrudRepository implements ScheduleRepositoryInterface
{

    public function tableName(): string
    {
        return 'queue_schedule';
    }

    public function getEntityClass(): string
    {
        return ScheduleEntity::class;
    }

    public function allByChannel(string $channel = null, Query $query = null): Enumerable
    {
        $query = $this->forgeQuery($query);
        if ($channel) {
            $query->where('channel', $channel);
        }
        return $this->findAll($query);
    }

    public function mappers(): array
    {
        return [
            new TimeMapper(['executed_at', 'created_at', 'updated_at']),
        ];
    }
}
