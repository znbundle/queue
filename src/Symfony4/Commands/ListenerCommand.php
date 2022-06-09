<?php

namespace ZnBundle\Queue\Symfony4\Commands;

use Psr\Container\ContainerInterface;
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
use ZnCore\Base\Libs\Container\Traits\ContainerAwareTrait;
use ZnCore\Base\Libs\FileSystem\Helpers\FilePathHelper;
use ZnCore\Base\Libs\Shell\CommandForm;
use ZnCore\Base\Libs\Shell\Helpers\CommandHelper;
use ZnCore\Base\Libs\Shell\ShellCommand;
use ZnSandbox\Sandbox\Process\Libs\LoopCron;
use ZnSandbox\Sandbox\Process\Libs\ProcessFix;

class ListenerCommand extends Command
{

    use ContainerAwareTrait;

    protected static $defaultName = 'queue:listener';
    private $jobService;
    private $cron;

    public function __construct(?string $name = null, JobServiceInterface $jobService, ContainerInterface $container)
    {
        parent::__construct($name);
        $this->jobService = $jobService;
        $this->setContainer($container);
    }

    protected function configure()
    {
        $this->addArgument('channel', InputArgument::OPTIONAL);

        $this
            ->addOption(
                'wrapped',
                null,
                InputOption::VALUE_OPTIONAL,
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
        ], $this->getContainer());
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

        /*$commandForm = new CommandForm();
        $commandForm->setPath($path);
        $commandForm->setLang('en_GB');
//        $commandForm->setCommand('php');
        $commandForm->setArguments([
            'php',
            'zn',
            'queue:run',
            $channel,
            "--wrapped" => 1,
        ]);

        $commandString = CommandHelper::getCommandString($commandForm);

        dd($commandString);
        $process = Process::fromShellCommandline($commandString, $commandForm->getPath());*/

        /*$commandString = CommandHelper::argsToString([
            'php',
            'zn',
            'queue:run',
            $channel,
            "--wrapped" => 1,
        ]);*/
        $commandString = "php zn queue:run $channel --wrapped=1";
//        dd($commandString);

        $process = Process::fromShellCommandline($commandString);


//        $process = new Process($commandForm->getCommand(), $commandForm->getPath());

        /*$process = new Process([
            'php',
            'zn',
            'queue:run',
            $channel,
            "--wrapped=1",
        ], $path);*/



        //$process->setCommandLine();

//        exec($process->getCommandLine());

       // dd($process->getCommandLine());

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
