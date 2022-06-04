<?php

namespace ZnBundle\Queue\Domain\Services;

use Illuminate\Support\Collection;
use Psr\Container\ContainerInterface;
use ZnBundle\Queue\Domain\Entities\JobEntity;
use ZnBundle\Queue\Domain\Entities\TotalEntity;
use ZnBundle\Queue\Domain\Enums\PriorityEnum;
use ZnBundle\Queue\Domain\Interfaces\JobInterface;
use ZnBundle\Queue\Domain\Interfaces\Repositories\JobRepositoryInterface;
use ZnBundle\Queue\Domain\Interfaces\Services\JobServiceInterface;
use ZnBundle\Queue\Domain\Interfaces\Services\ScheduleServiceInterface;
use ZnBundle\Queue\Domain\Queries\NewTaskQuery;
use ZnCore\Base\Libs\DotEnv\DotEnv;
use ZnCore\Domain\Base\BaseService;
use ZnCore\Domain\Helpers\EntityHelper;
use ZnCore\Domain\Helpers\ValidationHelper;
use ZnCore\Domain\Interfaces\Libs\EntityManagerInterface;

/**
 * @method JobEntity createEntity(array $attributes = [])
 * @method JobRepositoryInterface getRepository()
 */
class JobService extends BaseService implements JobServiceInterface
{

    protected $container;
    protected $scheduleService;

    public function __construct(
        JobRepositoryInterface $repository,
        ScheduleServiceInterface $scheduleService,
        ContainerInterface $container,
        EntityManagerInterface $entityManager
    )
    {
        $this->setRepository($repository);
        $this->container = $container;
        $this->setEntityManager($entityManager);
        $this->scheduleService = $scheduleService;
    }

    public function push(JobInterface $job, int $priority = PriorityEnum::NORMAL, string $channel = null)
    {
        //$isAvailable = $this->beforeMethod([$this, 'push']);
        $jobEntity = $this->createEntity();
        $jobEntity->setChannel($channel);
        $jobEntity->setJob($job);
        $jobEntity->setPriority($priority);
        //$jobEntity->setDelay();
        ValidationHelper::validateEntity($jobEntity);

        if (DotEnv::get('CRON_DIRECT_RUN', false) == 1) {
            $jobInstance = $this->getJobInstance($jobEntity, $this->container);
            $jobInstance->run();
            return $jobEntity;
        }

        $this->getRepository()->create($jobEntity);

        if (DotEnv::get('CRON_AUTORUN', false) == 1) {
            $this->runAll();
        }

        return $jobEntity;
    }

    public function newTasks(string $channel = null): Collection
    {
        $query = new NewTaskQuery($channel);
        $jobCollection = $this->getRepository()->all($query);
        return $jobCollection;
    }

    public function runAll(string $channel = null): TotalEntity
    {
        $scheduleJobCollection = $this->scheduleService->runAll($channel);
        $this->persistCollection($scheduleJobCollection);

//        dd($scheduleJobCollection);

        $query = new NewTaskQuery($channel);
        /** @var Collection | JobEntity[] $jobCollection */
        $jobCollection = $this->getRepository()->all($query);
        $totalEntity = new TotalEntity;
        foreach ($jobCollection as $jobEntity) {
            $isSuccess = $this->runJob($jobEntity);
            if ($isSuccess) {
                $totalEntity->incrementSuccess($jobEntity);
            } else {
                $totalEntity->incrementFail($jobEntity);
            }
        }
        return $totalEntity;
    }

    /**
     * @param Collection | JobEntity[] $collection
     */
    private function persistCollection(Collection $collection): void
    {
        if ($collection->isEmpty()) {
            return;
        }
        foreach ($collection as $jobEntity) {
            $this->getEntityManager()->persist($jobEntity);
        }
    }

    private function runJob(JobEntity $jobEntity)
    {
        $jobInstance = $this->getJobInstance($jobEntity, $this->container);
        $jobEntity->incrementAttempt();
        $isSuccess = false;

        $jobInstance->run();
        $jobEntity->setCompleted();
        $isSuccess = true;

        try {

        } catch (\Throwable $e) {

        }
        $this->getRepository()->update($jobEntity);
        return $isSuccess;
    }

    private function getJobInstance(JobEntity $jobEntity, ContainerInterface $container): JobInterface
    {
        $jobClass = $jobEntity->getClass();
        /** @var JobInterface $jobInstance */
        $jobInstance = $container->get($jobClass);
        //$jobInstance = DiHelper::make($jobClass, $container);
        $data = $jobEntity->getJob();
        EntityHelper::setAttributes($jobInstance, $data);
        return $jobInstance;
    }
}
