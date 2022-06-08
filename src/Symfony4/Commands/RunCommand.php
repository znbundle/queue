<?php

namespace ZnBundle\Queue\Symfony4\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\Store\SemaphoreStore;
use ZnBundle\Queue\Domain\Entities\TotalEntity;
use ZnBundle\Queue\Domain\Interfaces\Services\JobServiceInterface;
use ZnBundle\Queue\Symfony4\Widgets\TotalQueueWidget;
use ZnCore\Base\Helpers\ClassHelper;
use ZnLib\Console\Symfony4\Widgets\LogWidget;

class RunCommand extends Command
{

    protected static $defaultName = 'queue:run';
    private $jobService;

    /** @var LockInterface */
    private $lock;
    private $lockFactory;

    public function __construct(
        ?string $name = null,
        JobServiceInterface $jobService,
        LockFactory $lockFactory
    )
    {
        parent::__construct($name);
        $this->jobService = $jobService;
        $this->lockFactory = $lockFactory;
    }

    protected function configure()
    {
        $this->addArgument('channel', InputArgument::OPTIONAL);

        $this
            ->addOption(
                'wrapped',
                null,
                InputOption::VALUE_REQUIRED,
                '',
                false
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $wrapped = $input->getOption('wrapped');
        $channel = $input->getArgument('channel');

        if (!$wrapped) {
            $output->writeln('<fg=white># Queue run</>');
            $output->writeln('');
            if ($channel) {
                $output->writeln("Channel: <fg=blue>{$channel}</>");
            } else {
                $output->writeln("Channel: <fg=blue>all</>");
            }
            $output->writeln('');
        }


        $name = 'cronRun-' . ($channel ?: 'all');
//        $this->lock = $this->createLockInstance($name);
        $this->lock = $this->lockFactory->createLock($name, 30);
        if ($this->lock->acquire()) {
            try {
                $totalEntity = $this->runQueues($channel, $output);
                if (!$wrapped || $totalEntity->getAll()) {
                    $this->showTotal($totalEntity, $output);
                }
            } finally {
                $this->lock->release();
            }
        } else {
            $output->writeln('<fg=yellow>Locked!</>');
        }

        return 0;
    }

    protected function showTotal(TotalEntity $totalEntity, OutputInterface $output)
    {
        $totalWidget = new TotalQueueWidget($output);
        $totalWidget->run($totalEntity);
        $output->writeln('');
    }

    protected function runQueues($channel, OutputInterface $output): TotalEntity
    {
        $jobCollection = $this->jobService->newTasks($channel);
//        dd($jobCollection);

        $logWidget = new LogWidget($output);
        $logWidget->setPretty(true);
        $logWidget->setLineLength(64);

        $totalEntity = new TotalEntity;
        foreach ($jobCollection as $jobEntity) {
            $class = ClassHelper::getClassOfClassName($jobEntity->getClass());
            $time = (new \DateTime())->format('Y-m-d H:i:s');
            $label = "{$jobEntity->getId()} - {$jobEntity->getChannel()} - {$class} - $time";
            $logWidget->start($label);

//            $isSuccess = true;
//            usleep(mt_rand(10000, 1000000));

            $isSuccess = $this->jobService->runJob($jobEntity);
            if ($isSuccess) {
                $totalEntity->incrementSuccess($jobEntity);
                $logWidget->finishSuccess();
            } else {
                $totalEntity->incrementFail($jobEntity);
                $logWidget->finishFail();
            }

//            sleep(10);
            $this->lock->refresh();
        }
        return $totalEntity;
    }

    /*protected function createLockInstance(string $name): LockInterface
    {
//        $store = new SemaphoreStore();
//        $factory = new LockFactory($store);

        return $this->lockFactory->createLock($name, 30);
    }*/
}
