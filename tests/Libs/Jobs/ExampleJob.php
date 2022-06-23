<?php

namespace ZnBundle\Queue\Tests\Libs\Jobs;

use ZnBundle\Queue\Domain\Interfaces\JobInterface;
use ZnCore\Domain\Entity\Exceptions\AlreadyExistsException;
use ZnCore\Base\Libs\Container\Interfaces\ContainerAwareInterface;
use ZnCore\Base\Libs\Container\Traits\ContainerAwareTrait;

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