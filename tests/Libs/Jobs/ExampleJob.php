<?php

namespace ZnBundle\Queue\Tests\Libs\Jobs;

use ZnBundle\Queue\Domain\Interfaces\JobInterface;
use ZnCore\Base\Exceptions\AlreadyExistsException;
use ZnCore\Base\Libs\Container\ContainerAwareInterface;
use ZnCore\Base\Libs\Container\ContainerAwareTrait;

class ExampleJob implements JobInterface, ContainerAwareInterface
{

    use ContainerAwareTrait;

    public $messageText;

    public function run()
    {
        if($this->messageText == 'qwerty') {
            throw new AlreadyExistsException($this->messageText);
        }
    }

}