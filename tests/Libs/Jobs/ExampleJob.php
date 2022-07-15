<?php

namespace ZnBundle\Queue\Tests\Libs\Jobs;

use ZnBundle\Queue\Domain\Interfaces\JobInterface;
use ZnDomain\Entity\Exceptions\AlreadyExistsException;
use ZnCore\Container\Interfaces\ContainerAwareInterface;
use ZnCore\Container\Traits\ContainerAwareTrait;

class ExampleJob implements JobInterface//, ContainerAwareInterface
{

    use ContainerAwareTrait;

    public $messageText;

    public function run()
    {
        if ($this->messageText == 'qwerty') {
            throw new AlreadyExistsException($this->messageText);
        }
    }

}