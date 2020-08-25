<?php

namespace PhpBundle\Queue\Domain\Queries;

use PhpLab\Core\Domain\Entities\Query\Where;
use PhpLab\Core\Enums\StatusEnum;

class NewTaskQuery extends TaskOrderQuery
{

    public function __construct(string $channel = null)
    {
        parent::__construct();
        $this->addStatusFilter();
        if ($channel) {
            $this->addChannelFilter($channel);
        }
    }

    private function addStatusFilter()
    {
        $whereEnabled = new Where('status', StatusEnum::ENABLE);
        $this->whereNew($whereEnabled);
    }

    private function addChannelFilter(string $channel = null)
    {
        $whereChannel = new Where('channel', $channel);
        $this->whereNew($whereChannel);
    }
}
