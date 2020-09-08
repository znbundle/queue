<?php

namespace ZnBundle\Queue\Domain\Queries;

use ZnCore\Base\Domain\Libs\Query;

class TaskOrderQuery extends Query
{

    public function __construct()
    {
        $this->orderBy([
            'priority' => SORT_DESC,
            'pushed_at' => SORT_ASC,
        ]);
    }

}
