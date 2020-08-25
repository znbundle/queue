<?php

namespace PhpBundle\Queue\Domain\Queries;

use PhpLab\Core\Domain\Libs\Query;

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
