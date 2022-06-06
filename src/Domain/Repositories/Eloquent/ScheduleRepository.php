<?php

namespace ZnBundle\Queue\Domain\Repositories\Eloquent;

use Illuminate\Support\Collection;
use ZnBundle\Queue\Domain\Entities\ScheduleEntity;
use ZnBundle\Queue\Domain\Interfaces\Repositories\ScheduleRepositoryInterface;
use ZnCore\Domain\Libs\Query;
use ZnDatabase\Base\Domain\Mappers\TimeMapper;
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

    public function allByChannel(string $channel = null, Query $query = null): Collection
    {
        $query = $this->forgeQuery($query);
        if($channel) {
            $query->where('channel', $channel);
        }
        return $this->all($query);
    }

    public function mappers(): array
    {
        return [
            new TimeMapper(['executed_at', 'created_at', 'updated_at']),
        ];
    }
}
