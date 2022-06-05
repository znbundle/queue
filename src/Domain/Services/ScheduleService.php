<?php

namespace ZnBundle\Queue\Domain\Services;

use Illuminate\Support\Collection;
use ZnBundle\Queue\Domain\Entities\JobEntity;
use ZnBundle\Queue\Domain\Entities\ScheduleEntity;
use ZnBundle\Queue\Domain\Interfaces\Repositories\ScheduleRepositoryInterface;
use ZnBundle\Queue\Domain\Interfaces\Services\ScheduleServiceInterface;
use ZnCore\Domain\Base\BaseCrudService;
use ZnCore\Domain\Behaviors\SoftDeleteBehavior;
use ZnCore\Domain\Interfaces\Libs\EntityManagerInterface;
use ZnCore\Domain\Libs\Query;

/**
 * @method ScheduleRepositoryInterface getRepository()
 */
class ScheduleService extends BaseCrudService implements ScheduleServiceInterface
{

    public function __construct(EntityManagerInterface $em)
    {
        $this->setEntityManager($em);
    }

    public function getEntityClass(): string
    {
        return ScheduleEntity::class;
    }

    public function subscribes(): array
    {
        return [
            SoftDeleteBehavior::class,
        ];
    }

    public function allByChannel(string $channel = null, Query $query = null): Collection
    {
        $query = $this->forgeQuery($query);
        $collection = $this->getRepository()->allByChannel($channel, $query);
        return $collection;
    }

    public function runAll(string $channel = null): Collection
    {
        $jobCollection = new Collection();
        $collection = $this->allByChannel($channel);
        if(!$collection->isEmpty()) {
            foreach ($collection as $scheduleEntity) {
                // todo: use - https://packagist.org/packages/dragonmantank/cron-expression
                // https://crontab.guru/

                $jobEntity = new JobEntity();
                $jobEntity->setChannel($scheduleEntity->getChannel());
                $jobEntity->setClass($scheduleEntity->getClass());
                $jobEntity->setData($scheduleEntity->getData());
//            $jobEntity->setPriority();

                $jobCollection->add($jobEntity);
            }
        }
        return $jobCollection;
    }
}
