<?php

namespace ZnBundle\Queue\Domain\Services;

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
use ZnCore\Code\Helpers\PropertyHelper;
use ZnCore\Collection\Interfaces\Enumerable;
use ZnCore\DotEnv\Domain\Libs\DotEnv;
use ZnDomain\Entity\Helpers\EntityHelper;
use ZnDomain\EntityManager\Interfaces\EntityManagerInterface;
use ZnDomain\Service\Base\BaseService;
use ZnDomain\Validator\Helpers\ValidationHelper;
use ZnLib\Components\Status\Enums\StatusEnum;

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

    public function newTasks(string $channel = null): Enumerable
    {
        $scheduleJobCollection = $this->scheduleService->runAll($channel);
//        $this->persistCollection($scheduleJobCollection);
        $query = new NewTaskQuery($channel);
        $jobCollection = $this->getRepository()->findAll($query);
        return $jobCollection;
    }

    public function runAll(string $channel = null): TotalEntity
    {
//        $scheduleJobCollection = $this->scheduleService->runAll($channel);
//        $this->persistCollection($scheduleJobCollection);

//        dd($scheduleJobCollection);

        $jobCollection = $this->newTasks($channel);

//        $query = new NewTaskQuery($channel);
        /** @var \ZnCore\Collection\Interfaces\Enumerable | JobEntity[] $jobCollection */
//        $jobCollection = $this->getRepository()->findAll($query);


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
     * @param Enumerable | JobEntity[] $collection
     */
    private function persistCollection(Enumerable $collection): void
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
            if ($jobEntity->getAttempt() >= 3) {
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
        PropertyHelper::setAttributes($jobInstance, $data);
        return $jobInstance;
    }
}
