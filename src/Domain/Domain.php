<?php

namespace ZnBundle\Queue\Domain;

use ZnCore\Domain\Interfaces\DomainInterface;

class Domain implements DomainInterface
{

    public function getName()
    {
        return 'queue';
    }

}