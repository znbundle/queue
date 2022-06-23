<?php

namespace ZnBundle\Queue\Domain\Services;

use Illuminate\Support\Collection;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use ZnBundle\Queue\Domain\Entities\JobEntity;
use ZnBundle\Queue\Domain\Entities\TotalEntity;
use ZnBundle\Queue\Domain\Enums\PriorityEnum;
use ZnBundle\Queue\Domain\Interfaces\JobInterface;
use ZnBundle\Queue\Domain\Interfaces\Repositories\JobRepositoryInterface;
use ZnBundle\Queue\Domain\Interfaces\Services\JobServiceInterface;
use ZnBundle\Queue\Domain\Interfaces\Services\ScheduleServiceInterface;
use ZnBundle\Queue\Domain\Queries\NewTaskQuery;
use ZnCore\Base\Status\Enums\StatusEnum;
use ZnCore\Base\DotEnv\Domain\Libs\DotEnv;
use ZnCore\Domain\Service\Base\BaseService;
use ZnCore\Domain\Entity\Helpers\EntityHelper;
use ZnCore\Base\Validation\Helpers\ValidationHelper;
use ZnCore\Domain\EntityManager\Interfaces\EntityManagerInterface;

/**
 * @method JobEntity createEntity(array $attributes = [])
 * @method JobRepositoryInterface getRepository()
 */
class JobService extends BaseService implements JobServiceInterface
{

    protected $container;
    protected $scheduleService;
    protected $logger;

    public function __construct(
        JobRepositoryInterface $repository,
        ScheduleServiceInterface $scheduleService,
        ContainerInterface $container,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    )
    {
        $this->setRepository($repository);
        $this->container = $container;
        $this->setEntityManager($entityManager);
        $this->scheduleService = $scheduleService;
        $this->logger = $logger;
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

        $this->touch();

        return $jobEntity;
    }

    public function touch(): void
    {
        if (DotEnv::get('CRON_AUTORUN', false) == 1) {
            $this->runAll();
        }
    }

    public function newTasks(string $channel = null): Collection
    {
        $scheduleJobCollection = $this->scheduleService->runAll($channel);
//        $this->persistCollection($scheduleJobCollection);
        $query = new NewTaskQuery($channel);
        $jobCollection = $this->getRepository()->all($query);
        return $jobCollection;
    }

    public function runAll(string $channel = null): TotalEntity
    {
//        $scheduleJobCollection = $this->scheduleService->runAll($channel);
//        $this->persistCollection($scheduleJobCollection);

//        dd($scheduleJobCollection);

        $jobCollection = $this->newTasks($channel);

//        $query = new NewTaskQuery($channel);
        /** @var Collection | JobEntity[] $jobCollection */
//        $jobCollection = $this->getRepository()->all($query);


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

    public function runJob(JobEntity $jobEntity)
    {
        $jobInstance = $this->getJobInstance($jobEntity, $this->container);
        $jobEntity->incrementAttempt();
        $isSuccess = false;


        $logContext = [
            'job' => EntityHelper::toArray($jobEntity, true),
        ];

        try {
            $jobInstance->run();
            $jobEntity->setCompleted();
            $isSuccess = true;
        } catch (\Throwable $e) {
            $logContext['error'] = EntityHelper::toArray($e, true);
            if($jobEntity->getAttempt() >= 3) {
                $jobEntity->setStatus(StatusEnum::BLOCKED);
            }
        }
        $this->getRepository()->update($jobEntity);

        if ($isSuccess) {
            $this->logger->info('CRON task run success', $logContext);
        } else {
            $this->logger->error('CRON task run fail', $logContext);
        }

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
