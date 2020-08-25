<?php

namespace PhpBundle\Queue\Tests\Libs\Jobs;

use PhpBundle\Queue\Domain\Interfaces\JobInterface;
use PhpLab\Core\Exceptions\AlreadyExistsException;
use PhpLab\Core\Libs\Container\ContainerAwareInterface;
use PhpLab\Core\Libs\Container\ContainerAwareTrait;

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