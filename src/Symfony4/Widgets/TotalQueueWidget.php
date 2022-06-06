<?php

namespace ZnBundle\Queue\Symfony4\Widgets;

use Symfony\Component\Console\Output\OutputInterface;
use ZnBundle\Queue\Domain\Entities\TotalEntity;

class TotalQueueWidget
{

    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function run(TotalEntity $totalEntity): void
    {
        $output = $this->output;
        $isEmpty = $totalEntity->getAll() == 0;
        if ($isEmpty) {
            $output->writeln('<fg=magenta>Jobs empty!</>');
            return;
        }
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        if ($totalEntity->getSuccess()) {
            $message = '<fg=green>Complete ' . $totalEntity->getSuccess() . ' jobs!</> - ' . $now;
            $output->writeln($message);
        }
        if ($totalEntity->getFail()) {
            $message = '<fg=red>Error ' . $totalEntity->getFail() . ' jobs!</> - ' . $now;
            $output->writeln($message);
        }
    }
}
