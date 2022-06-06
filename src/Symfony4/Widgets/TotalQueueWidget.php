<?php

namespace ZnBundle\Queue\Symfony4\Widgets;

use Symfony\Component\Console\Output\OutputInterface;

class TotalQueueWidget
{

    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function run(): void
    {
        $output = $this->output;
        $isEmpty = $total->getSuccess() + $total->getFail() == 0;
        if ($isEmpty) {
            $output->writeln('<fg=magenta>Jobs empty!</>');
            return;
        }
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        if ($total->getSuccess()) {
            $message = '<fg=green>Complete ' . $total->getSuccess() . ' jobs!</> - ' . $now;
            $output->writeln($message);
        }
        if ($total->getFail()) {
            $message = '<fg=red>Error ' . $total->getFail() . ' jobs!</> - ' . $now;
            $output->writeln($message);
        }
    }
}
