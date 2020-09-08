<?php

namespace ZnBundle\Queue\Domain;

use ZnCore\Base\Domain\Interfaces\DomainInterface;

class Domain implements DomainInterface
{

    public function getName()
    {
        return 'queue';
    }

}