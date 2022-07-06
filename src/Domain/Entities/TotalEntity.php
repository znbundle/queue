<?php

namespace ZnBundle\Queue\Domain\Entities;

use ZnCore\Collection\Libs\Collection;

class TotalEntity
{

    private $all = 0;
    private $success = 0;
    private $fail = 0;
    private $successCollection;
    private $failCollection;

    public function __construct()
    {
        $this->successCollection = new Collection();
        $this->failCollection = new Collection();
    }

    public function incrementSuccess(JobEntity $jobEntity)
    {
        $this->success++;
        $this->successCollection->add($jobEntity);
    }

    public function incrementFail(JobEntity $jobEntity)
    {
        $this->fail++;
        $this->failCollection->add($jobEntity);
    }

    public function getAll(): int
    {
        return $this->getFail() + $this->getSuccess();
    }

    public function getSuccess(): int
    {
        return $this->success;
    }

    public function getFail(): int
    {
        return $this->fail;
    }

}
