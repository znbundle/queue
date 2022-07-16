<?php

namespace ZnBundle\Queue\Tests\Unit;

use ZnCore\Container\Interfaces\ContainerConfiguratorInterface;
use ZnCore\Container\Libs\Container;
use Illuminate\Database\Capsule\Manager;
use ZnBundle\Queue\Domain\Enums\PriorityEnum;
use ZnBundle\Queue\Domain\Interfaces\Repositories\JobRepositoryInterface;
use ZnBundle\Queue\Domain\Interfaces\Services\JobServiceInterface;
use ZnBundle\Queue\Domain\Repositories\Eloquent\JobRepository;
use ZnBundle\Queue\Domain\Services\JobService;
use ZnBundle\Queue\Tests\Libs\Jobs\ExampleJob;
use ZnCore\Container\Helpers\ContainerHelper;
use ZnCore\Collection\Helpers\CollectionHelper;
use ZnDomain\Entity\Helpers\EntityHelper;
use ZnTool\Test\Base\BaseTest;
use Psr\Container\ContainerInterface;

final class JobTest extends BaseTest
{

    const CHANNEL_EMAIL = 'email';
    const CHANNEL_SMS = 'sms';

    /** @var ContainerInterface */
    private $container;

    protected function fixtures(): array
    {
        return [
            'queue_job',
        ];
    }

    private function makeContainer(): ContainerInterface
    {
        $container = ContainerHelper::getContainer();

        /** @var ContainerConfiguratorInterface $containerConfigurator */
        $containerConfigurator = $container->get(ContainerConfiguratorInterface::class);
//        $containerConfigurator = ContainerHelper::getContainerConfiguratorByContainer($container);
        $containerConfigurator->bind(Manager::class, \ZnDatabase\Eloquent\Domain\Capsule\Manager::class, true);
        $containerConfigurator->bind(JobRepositoryInterface::class, JobRepository::class, true);
        $containerConfigurator->bind(JobServiceInterface::class, JobService::class, true);
        $containerConfigurator->bind(ContainerInterface::class, Container::class, true);


        /*$container->bind(Manager::class, \ZnDatabase\Eloquent\Domain\Capsule\Manager::class, true);
        $container->bind(JobRepositoryInterface::class, JobRepository::class, true);
        $container->bind(JobServiceInterface::class, JobService::class, true);
        $container->bind(ContainerInterface::class, Container::class, true);*/
        return $container;
    }

    private function clearQueue()
    {
        $jobRepository = $this->container->get(JobRepositoryInterface::class);
        $jobRepository->deleteByCondition([]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = $this->makeContainer();
        $this->clearQueue();
    }

    public function testRunAllSuccess()
    {
        $this->markTestIncomplete();
        
        $jobService = $this->container->get(JobServiceInterface::class);

        $job = new ExampleJob;
        $job->messageText = 'qwerty123';
        $pushResult = $jobService->push($job, PriorityEnum::NORMAL, self::CHANNEL_EMAIL);

        $jobCollection = $jobService->newTasks();

        $this->assertArraySubset([
            [
                'channel' => self::CHANNEL_EMAIL,
                'class' => ExampleJob::class,
                //'data' => 'YToxOntzOjExOiJtZXNzYWdlVGV4dCI7czo5OiJxd2VydHkxMjMiO30=',
                'job' => [
                    'messageText' => 'qwerty123'
                ],
                'priority' => PriorityEnum::NORMAL,
                'delay' => 0,
                'attempt' => 0,
            ],
        ], CollectionHelper::toArray($jobCollection));

        $totalEntity = $jobService->runAll(self::CHANNEL_SMS);
        $this->assertEquals(0, $totalEntity->getSuccess());

        $totalEntity = $jobService->runAll(self::CHANNEL_EMAIL);
        $this->assertEquals(1, $totalEntity->getSuccess());
    }

    public function testRunAllFail()
    {
        $this->markTestIncomplete();

        $jobService = $this->container->get(JobServiceInterface::class);

        $job = new ExampleJob;
        $job->messageText = 'qwerty';
        $pushResult = $jobService->push($job, PriorityEnum::NORMAL, self::CHANNEL_EMAIL);

        $jobCollection = $jobService->newTasks();
        $this->assertArraySubset([
            [
                'channel' => self::CHANNEL_EMAIL,
                'class' => ExampleJob::class,
                //'data' => 'YToxOntzOjExOiJtZXNzYWdlVGV4dCI7czo2OiJxd2VydHkiO30=',
                'job' => [
                    'messageText' => 'qwerty'
                ],
                'priority' => PriorityEnum::NORMAL,
                'delay' => 0,
                'attempt' => 0,
            ],
        ], CollectionHelper::toArray($jobCollection));

        $totalEntity = $jobService->runAll(self::CHANNEL_SMS);
        $this->assertEquals(0, $totalEntity->getSuccess());

        $totalEntity = $jobService->runAll(self::CHANNEL_EMAIL);
        $this->assertEquals(0, $totalEntity->getSuccess());

        $jobCollection = $jobService->newTasks();

        $this->assertArraySubset([
            [
                'channel' => self::CHANNEL_EMAIL,
                'class' => ExampleJob::class,
                //'data' => 'YToxOntzOjExOiJtZXNzYWdlVGV4dCI7czo2OiJxd2VydHkiO30=',
                'job' => [
                    'messageText' => 'qwerty'
                ],
                'priority' => PriorityEnum::NORMAL,
                'delay' => 0,
                'attempt' => 1,
            ],
        ], CollectionHelper::toArray($jobCollection));
    }

}