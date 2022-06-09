<?php

namespace ZnBundle\Queue\Symfony4\Commands;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Process\Process;
use ZnBundle\Queue\Domain\Interfaces\Services\JobServiceInterface;
use ZnBundle\Queue\Symfony4\Widgets\TotalQueueWidget;
use ZnCore\Base\Libs\Container\Traits\ContainerAwareTrait;
use ZnCore\Base\Libs\FileSystem\Helpers\FilePathHelper;
use ZnLib\Console\Domain\Exceptions\ShellException;
use ZnLib\Console\Domain\Helpers\CommandLineHelper;
use ZnLib\Console\Symfony4\Traits\LockTrait;
use ZnLib\Console\Symfony4\Traits\LoopTrait;

class ListenerCommand extends Command
{

    use ContainerAwareTrait;
    use LockTrait;
    use LoopTrait;

    protected static $defaultName = 'queue:listener';
    private $jobService;
    private $cron;

    public function __construct(
        ?string $name = null,
        JobServiceInterface $jobService,
        ContainerInterface $container,
        LockFactory $lockFactory
    )
    {
        parent::__construct($name);
        $this->jobService = $jobService;
        $this->setContainer($container);
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
        $channel = $input->getArgument('channel');
        $output->writeln('<fg=white># Queue listener</>');
        $output->writeln('');
        if ($channel) {
            $output->writeln("Channel: <fg=blue>{$channel}</>");
        } else {
            $output->writeln("Channel: <fg=blue>all</>");
        }
        $output->writeln("");

        $name = 'cronListener-' . ($channel ?: 'all');
        $this->setLoopInterval(1);
        $this->runProcessWithLock($input, $output, $name);
        return 0;
    }

    protected function runLoopItem(InputInterface $input, OutputInterface $output): void
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
     * @throws ShellException
     */
    protected function runWrapped(InputInterface $input, OutputInterface $output): void
    {
        $channel = $input->getArgument('channel');
        $this->runConsoleCommand($channel, $output);
    }

    protected function runConsoleCommand(?string $channel, OutputInterface $output)//: ?string
    {
        $path = FilePathHelper::rootPath() . '/vendor/zncore/base/bin';
        $commandString = CommandLineHelper::argsToString([
            'php',
            'zn',
            'queue:run',
            $channel,
            "--wrapped" => 1,
        ]);
//        $commandString = "php zn queue:run $channel --wrapped=1";

        $process = Process::fromShellCommandline($commandString);
        $tick = function ($type, $buffer) use ($output) {
            $output->write($buffer);
            $this->refreshLock();
        };
        $process->run($tick);
    }
}
