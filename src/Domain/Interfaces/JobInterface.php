<?php

namespace PhpBundle\Queue\Domain\Interfaces;

interface JobInterface
{

    public function run();

}