<?php

namespace ZnBundle\Queue\Symfony4\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\LockFactory;
use ZnBundle\Queue\Domain\Entities\TotalEntity;
use ZnBundle\Queue\Domain\Interfaces\Services\JobServiceInterface;
use ZnBundle\Queue\Symfony4\Widgets\TotalQueueWidget;
use ZnCore\Base\Instance\Helpers\ClassHelper;
use ZnLib\Console\Symfony4\Traits\IOTrait;
use ZnLib\Console\Symfony4\Traits\LockTrait;
use ZnLib\Console\Symfony4\Widgets\LogWidget;

class RunCommand extends Command
{

    use LockTrait;
    use IOTrait;

    protected static $defaultName = 'queue:run';
    private $jobService;

    public function __construct(
        ?string $name = null,
        JobServiceInterface $jobService,
        LockFactory $lockFactory
    )
    {
        parent::__construct($name);
        $this->jobService = $jobService;
        $this->setLockFactory($lockFactory);
    }

    protected function configure()
    {
        $this->addArgument('channel', InputArgument::OPTIONAL);
        $this->addOption(
            'wrapped',
            null,
            InputOption::VALUE_OPTIONAL,
            '',
            false
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setInputOutput($input, $output);
        $channel = $input->getArgument('channel');
        $name = 'cronRun-' . ($channel ?: 'all');
//        $this->runProcessWithLock($name);

        try {
            $this->runProcessWithLock($name);
        } catch (LockAcquiringException $e) {
            $output->writeln('<fg=yellow>' . $e->getMessage() . '</>');
            $output->writeln('');
        }

        return Command::SUCCESS;
    }

    protected function runProcess(): void
    {
        $input = $this->getInput();
        $output = $this->getOutput();
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

        $totalEntity = $this->runQueues($channel);
        if (!$wrapped || $totalEntity->getAll()) {
            $this->showTotal($totalEntity);
        }
    }

    protected function showTotal(TotalEntity $totalEntity)
    {
        $output = $this->getOutput();
        $totalWidget = new TotalQueueWidget($output);
        $totalWidget->run($totalEntity);
        $output->writeln('');
    }

    protected function runQueues($channel): TotalEntity
    {
        $output = $this->getOutput();
        $jobCollection = $this->jobService->newTasks($channel);

        $logWidget = new LogWidget($output);
        $logWidget->setPretty(true);
        $logWidget->setLineLength(64);

        $totalEntity = new TotalEntity;
        foreach ($jobCollection as $jobEntity) {
            $class = ClassHelper::getClassOfClassName($jobEntity->getClass());
            $time = (new \DateTime())->format('Y-m-d H:i:s');
            $label = "{$jobEntity->getId()} - {$jobEntity->getChannel()} - {$class} - $time";
            $logWidget->start($label);
            $isSuccess = $this->jobService->runJob($jobEntity);
            if ($isSuccess) {
                $totalEntity->incrementSuccess($jobEntity);
                $logWidget->finishSuccess();
            } else {
                $totalEntity->incrementFail($jobEntity);
                $logWidget->finishFail();
            }
            $this->refreshLock();
        }
        return $totalEntity;
    }
}
