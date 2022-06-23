<?php

namespace ZnBundle\Queue\Domain\Queries;

use ZnCore\Domain\Query\Entities\Where;
use ZnCore\Base\Libs\Status\Enums\StatusEnum;

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
        $whereEnabled = new Where('status', StatusEnum::ENABLED);
        $this->whereNew($whereEnabled);
    }

    private function addChannelFilter(string $channel = null)
    {
        $whereChannel = new Where('channel', $channel);
        $this->whereNew($whereChannel);
    }
}
