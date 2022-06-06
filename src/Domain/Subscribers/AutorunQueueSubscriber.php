<?php

namespace ZnBundle\Queue\Domain\Subscribers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;
use ZnBundle\Queue\Domain\Interfaces\Services\JobServiceInterface;
use ZnCore\Base\Libs\Container\Helpers\ContainerHelper;
use ZnSandbox\Sandbox\App\Enums\AppEventEnum;

class AutorunQueueSubscriber implements EventSubscriberInterface
{

    /*private $jobService;

    public function __construct(JobServiceInterface $jobService)
    {
        $this->jobService = $jobService;
    }*/

    public static function getSubscribedEvents(): array
    {
        return [
            AppEventEnum::AFTER_INIT_DISPATCHER => 'onAfterInit',
        ];
    }

    public function onAfterInit(Event $event)
    {
        $callback = function() {
            /** @var JobServiceInterface $jobService */
            $jobService = ContainerHelper::getContainer()->get(JobServiceInterface::class);
            $jobService->touch();
        };
        register_shutdown_function($callback);
    }
}
