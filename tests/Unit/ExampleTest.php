<?php

namespace PhpBundle\Queue\Tests\Unit;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use PhpBundle\Queue\Domain\Enums\PriorityEnum;
use PhpBundle\Queue\Domain\Interfaces\Repositories\JobRepositoryInterface;
use PhpBundle\Queue\Domain\Interfaces\Services\JobServiceInterface;
use PhpBundle\Queue\Domain\Repositories\Eloquent\JobRepository;
use PhpBundle\Queue\Domain\Services\JobService;
use PhpBundle\Queue\Tests\Libs\Jobs\ExampleJob;
use PhpLab\Core\Domain\Helpers\EntityHelper;
use PhpLab\Test\Base\BaseTest;
use Psr\Container\ContainerInterface;

final class ExampleTest extends BaseTest
{

    const CHANNEL_EMAIL = 'email';
    const CHANNEL_SMS = 'sms';

    /** @var ContainerInterface */
    private $container;

    private function makeContainer(): ContainerInterface
    {
        $container = Container::getInstance();
        $container->bind(Manager::class, \PhpLab\Eloquent\Db\Helpers\Manager::class, true);
        $container->bind(JobRepositoryInterface::class, JobRepository::class, true);
        $container->bind(JobServiceInterface::class, JobService::class, true);
        $container->bind(ContainerInterface::class, Container::class, true);
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
        ], EntityHelper::collectionToArray($jobCollection));

        $totalEntity = $jobService->runAll(self::CHANNEL_SMS);
        $this->assertEquals(0, $totalEntity->getSuccess());

        $totalEntity = $jobService->runAll(self::CHANNEL_EMAIL);
        $this->assertEquals(1, $totalEntity->getSuccess());
    }

    public function testRunAllFail()
    {
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
        ], EntityHelper::collectionToArray($jobCollection));

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
        ], EntityHelper::collectionToArray($jobCollection));
    }

}