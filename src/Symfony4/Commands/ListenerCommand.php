<?php

namespace ZnBundle\Queue\Symfony4\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ZnBundle\Queue\Domain\Interfaces\Services\JobServiceInterface;
use ZnBundle\Queue\Symfony4\Widgets\TotalQueueWidget;
use ZnCore\Base\Enums\Measure\TimeEnum;
use ZnCore\Base\Libs\FileSystem\Helpers\FilePathHelper;

class ListenerCommand extends Command
{

    protected static $defaultName = 'queue:listener';
    private $jobService;

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
        $output->writeln('<fg=white># Queue run</>');
        $output->writeln('');

        $channel = $input->getArgument('channel');
        if ($channel) {
            $output->writeln("Channel: <fg=blue>{$channel}</>");
        } else {
            $output->writeln("Channel: <fg=blue>all</>");
        }

        $output->writeln('');

        while (1 == 1) {
            usleep(1 / TimeEnum::SECOND_PER_MICROSECOND);
            $this->runAll($input, $output);
        }
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

    protected function runNormal(InputInterface $input, OutputInterface $output): void {
        $channel = $input->getArgument('channel');
        $totalEntity = $this->jobService->runAll($channel);
        if ($totalEntity->getAll()) {
            $totalWidget = new TotalQueueWidget($output);
            $totalWidget->run($totalEntity);
        }
    }

    protected function runWrapped(InputInterface $input, OutputInterface $output): void {
        $channel = $input->getArgument('channel');
        $path = FilePathHelper::rootPath() . '/vendor/zncore/base/bin';

        $cmd = new \ZnLib\Console\Symfony4\Libs\Command();
        $cmd->add("cd {$path}");
        $cmd->add("php zn queue:run {$channel} --wrapped=1");
        $command = $cmd->toString();

        $commandOutput = shell_exec($command);
        $commandOutput = trim($commandOutput);
        if ($commandOutput) {
            $output->writeln($commandOutput);
        }
    }
}
