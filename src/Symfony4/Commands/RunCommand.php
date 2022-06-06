<?php

namespace ZnBundle\Queue\Symfony4\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ZnBundle\Queue\Domain\Interfaces\Services\JobServiceInterface;
use ZnBundle\Queue\Symfony4\Widgets\TotalQueueWidget;

class RunCommand extends Command
{

    protected static $defaultName = 'queue:run';
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
        $wrapped = $input->getOption('wrapped');
        $channel = $input->getArgument('channel');

        if(!$wrapped) {
            $output->writeln('<fg=white># Queue run</>');
            $output->writeln('');


            if ($channel) {
                $output->writeln("Channel: <fg=blue>{$channel}</>");
            } else {
                $output->writeln("Channel: <fg=blue>all</>");
            }

            $output->writeln('');
        }

        $totalEntity = $this->jobService->runAll($channel);

        if(!$wrapped || $totalEntity->getAll()) {
            $totalWidget = new TotalQueueWidget($output);
            $totalWidget->run($totalEntity);
            $output->writeln('');
        }

        return 0;
    }
}
