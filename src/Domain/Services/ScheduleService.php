<?php

namespace ZnBundle\Queue\Domain\Services;

use Cron\CronExpression;
use DateTime;
use Psr\Log\LoggerInterface;
use ZnBundle\Queue\Domain\Entities\JobEntity;
use ZnBundle\Queue\Domain\Entities\ScheduleEntity;
use ZnBundle\Queue\Domain\Interfaces\Repositories\ScheduleRepositoryInterface;
use ZnBundle\Queue\Domain\Interfaces\Services\ScheduleServiceInterface;
use ZnCore\Collection\Interfaces\Enumerable;
use ZnCore\Collection\Libs\Collection;
use ZnDomain\EntityManager\Interfaces\EntityManagerInterface;
use ZnCore\Query\Entities\Query;
use ZnDomain\Service\Base\BaseCrudService;
use ZnLib\Components\SoftDelete\Subscribers\SoftDeleteSubscriber;

/**
 * @method ScheduleRepositoryInterface getRepository()
 */
class ScheduleService extends BaseCrudService implements ScheduleServiceInterface
{

    private $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->setEntityManager($em);
        $this->logger = $logger;
    }

    public function getEntityClass(): string
    {
        return ScheduleEntity::class;
    }

    public function subscribes(): array
    {
        return [
            SoftDeleteSubscriber::class,
        ];
    }

    public function allByChannel(string $channel = null, Query $query = null): Enumerable
    {
        $query = $this->forgeQuery($query);
        $collection = $this->getRepository()->allByChannel($channel, $query);
        return $collection;
    }

    public function runAll(string $channel = null): Enumerable
    {
        $jobCollection = new Collection();
        /** @var ScheduleEntity[] $collection */
        $collection = $this->allByChannel($channel);
        if (!$collection->isEmpty()) {
            foreach ($collection as $scheduleEntity) {
                if ($this->isDue($scheduleEntity)) {
                    $jobEntity = new JobEntity();
                    $jobEntity->setChannel($scheduleEntity->getChannel());
                    $jobEntity->setClass($scheduleEntity->getClass());
                    $jobEntity->setData($scheduleEntity->getData());
//                    $jobEntity->setPriority();
                    $this->getEntityManager()->persist($jobEntity);
//                    $jobCollection->add($jobEntity);
                    $this->updateExecutedAt($scheduleEntity);
                }
            }
        }
        return $jobCollection;
    }

    protected function updateExecutedAt(ScheduleEntity $scheduleEntity): void
    {
        $now = new DateTime();
        $scheduleEntity->setExecutedAt($now);
        $this->getEntityManager()->persist($scheduleEntity);
    }

    protected function isDue(ScheduleEntity $scheduleEntity): bool
    {
        $executedAt = $scheduleEntity->getExecutedAt();
        if (!$executedAt) {
            return true;
        }
        $dueTime = $this->dueTime($scheduleEntity);
        $isDue = $dueTime >= 0;
        return $isDue;
    }

    protected function dueTime(ScheduleEntity $scheduleEntity): int
    {
        $nextTime = $this->getNextTimeByScheduleEntity($scheduleEntity);
        $now = new DateTime();
        $dueTime = $now->getTimestamp() - $nextTime->getTimestamp();
        return $dueTime;
    }

    protected function getNextTimeByScheduleEntity(ScheduleEntity $scheduleEntity): DateTime
    {
        $executedAt = $scheduleEntity->getExecutedAt();
        $expression = $scheduleEntity->getExpression();
        $cron = new CronExpression($expression);
        return $cron->getNextRunDate($executedAt);
    }
}


// todo: use - https://packagist.org/packages/dragonmantank/cron-expression
// https://crontab.guru/
