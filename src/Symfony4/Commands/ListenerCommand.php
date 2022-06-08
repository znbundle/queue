<?php

namespace ZnBundle\Queue\Symfony4\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Process\Process;
use ZnBundle\Queue\Domain\Interfaces\Services\JobServiceInterface;
use ZnBundle\Queue\Symfony4\Widgets\TotalQueueWidget;
use ZnCore\Base\Helpers\InstanceHelper;
use ZnCore\Base\Libs\Container\Helpers\ContainerHelper;
use ZnCore\Base\Libs\FileSystem\Helpers\FilePathHelper;
use ZnCore\Base\Libs\Shell\ShellCommand;
use ZnSandbox\Sandbox\Process\Libs\LoopCron;
use ZnSandbox\Sandbox\Process\Libs\ProcessFix;

class ListenerCommand extends Command
{

    protected static $defaultName = 'queue:listener';
    private $jobService;
    private $cron;

    public function __construct(?string $name = null, JobServiceInterface $jobService)
    {
        parent::__construct($name);
        $this->jobService = $jobService;
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
        $output->writeln('<fg=white># Queue listener</>');
        $output->writeln('');

        $channel = $input->getArgument('channel');
        if ($channel) {
            $output->writeln("Channel: <fg=blue>{$channel}</>");
        } else {
            $output->writeln("Channel: <fg=blue>all</>");
        }

        $name = 'cronListener-' . ($channel ?: 'all');
        $callback = function () use ($input, $output) {
            $this->runAll($input, $output);
        };
//        $this->cron = new LoopCron($name);
        $this->cron = InstanceHelper::create(LoopCron::class, [
            'name' => $name,
        ]);
        $this->cron->setCallback($callback);

        try {
            $this->cron->start();
        } catch (LockConflictedException $e) {
            $output->writeln($e->getMessage());
        }

        /*while (1 == 1) {
            usleep(1 / TimeEnum::SECOND_PER_MICROSECOND);
            $this->runAll($input, $output);
        }*/

        return 0;
    }

    protected function runAll(InputInterface $input, OutputInterface $output): void
    {
        $wrapped = $input->getOption('wrapped');
        $channel = $input->getArgument('channel');
        if ($wrapped) {
            $this->runWrapped($input, $output);
        } else {
            $this->runNormal($input, $output);
        }
    }

    /**
     * Выполение очереди задач в текущем потоке
     *
     * Преимущества:
     * - отъедает чуть меньше ресурсов, так как консольное приложение уже загружено
     *
     * Недостатки:
     * - при деплое необходимо перезапускать демон
     * - система менее устойчива к ошибкам, если что-то поломалось в задаче, то демон может умереть
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function runNormal(InputInterface $input, OutputInterface $output): void
    {
        $channel = $input->getArgument('channel');
        $totalEntity = $this->jobService->runAll($channel);
        if ($totalEntity->getAll()) {
            $totalWidget = new TotalQueueWidget($output);
            $totalWidget->run($totalEntity);
        }
    }

    /**
     * Выполнение очереди задач как вызов консольной команды
     *
     * Преимущества:
     * - при деплое нет необходимости перезапускать демон
     * - система более устойчива к ошибкам, если что-то поломалось в задаче, то демон продолжает работать
     *
     * Недостатки:
     * - отъедает чуть больше ресурсов, так как каждый раз вызывается консольное приложение
     *
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \ZnCore\Base\Libs\Shell\ShellException
     */
    protected function runWrapped(InputInterface $input, OutputInterface $output): void
    {
        $channel = $input->getArgument('channel');
        $this->runConsoleCommand($channel, $output);

        /*$commandOutput = trim($commandOutput);
        if ($commandOutput) {
            $output->writeln($commandOutput);
        }*/
    }

    protected function runConsoleCommand(?string $channel, OutputInterface $output)//: ?string
    {
        $path = FilePathHelper::rootPath() . '/vendor/zncore/base/bin';

//        $processFix = new ProcessFix();
//        dd($_ENV);
//        $processFix->backupEnv();
        $process = new Process([
            'php',
            'zn',
            'queue:run',
            $channel,
            "--wrapped=1",
        ], $path);
//        $process->run();


        $tick = function ($type, $buffer) use ($output) {
            $output->write($buffer);
            $this->cron->tick();
            /*if (Process::ERR === $type) {
                $output->writeln('ERR > '.$buffer);
            } else {
                $output->writeln('OUT > '.$buffer);
            }*/
        };

        $process->run($tick);
//        $processFix->restoreEnv();

//        $commandOutput = $process->getOutput();
//        return $commandOutput;
    }

    protected function runConsoleCommand111111($channel): ?string
    {
        $runCommand = [
            "php zn queue:run",
            $channel,
            [
                "--wrapped" => true
            ],
        ];
        $path = FilePathHelper::rootPath() . '/vendor/zncore/base/bin';
        $shellCommand = new ShellCommand();
        $shellCommand->setPath($path);
        $shellResultEntity = $shellCommand->run($runCommand);
        $commandOutput = $shellResultEntity->getOutputString();
        return $commandOutput;
    }
}
