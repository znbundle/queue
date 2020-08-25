<?php

namespace PhpBundle\Queue\Domain\Services;

use Illuminate\Support\Collection;
use PhpBundle\Queue\Domain\Entities\JobEntity;
use PhpBundle\Queue\Domain\Entities\TotalEntity;
use PhpBundle\Queue\Domain\Enums\PriorityEnum;
use PhpBundle\Queue\Domain\Interfaces\JobInterface;
use PhpBundle\Queue\Domain\Interfaces\Repositories\JobRepositoryInterface;
use PhpBundle\Queue\Domain\Interfaces\Services\JobServiceInterface;
use PhpBundle\Queue\Domain\Queries\NewTaskQuery;
use PhpLab\Core\Domain\Base\BaseService;
use PhpLab\Core\Domain\Helpers\EntityHelper;
use PhpLab\Core\Domain\Helpers\ValidationHelper;
use PhpLab\Core\Helpers\DiHelper;
use Psr\Container\ContainerInterface;

/**
 * @method JobEntity createEntity(array $attributes = [])
 * @method JobRepositoryInterface getRepository()
 */
class JobService extends BaseService implements JobServiceInterface
{

    protected $container;

    public function __construct(JobRepositoryInterface $repository, ContainerInterface $container)
    {
        $this->repository = $repository;
        $this->container = $container;
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
        $this->getRepository()->create($jobEntity);
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

    private function runJob(JobEntity $jobEntity)
    {
        $jobInstance = $this->getJobInstance($jobEntity, $this->container);
        $jobEntity->incrementAttempt();
        $isSuccess = false;
        try {
            $jobInstance->run();
            $jobEntity->setCompleted();
            $isSuccess = true;
        } catch (\Throwable $e) {
        }
        $this->getRepository()->update($jobEntity);
        return $isSuccess;
    }

    private function getJobInstance(JobEntity $jobEntity, ContainerInterface $container): JobInterface
    {
        $jobClass = $jobEntity->getClass();
        /** @var JobInterface $jobInstance */
        $jobInstance = DiHelper::make($jobClass, $container);
        $data = $jobEntity->getJob();
        EntityHelper::setAttributes($jobInstance, $data);
        return $jobInstance;
    }
}
