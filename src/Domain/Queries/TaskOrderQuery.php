<?php

namespace ZnBundle\Queue\Domain\Queries;

use ZnCore\Code\Helpers\DeprecateHelper;
use ZnDomain\Query\Entities\Query;

DeprecateHelper::hardThrow();

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
