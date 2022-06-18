<?php

namespace ZnBundle\Queue\Domain\Subscribers;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Process\Process;
use Symfony\Contracts\EventDispatcher\Event;
use ZnBundle\Queue\Domain\Interfaces\Services\JobServiceInterface;
use ZnCore\Base\Libs\Container\Helpers\ContainerHelper;
use ZnLib\Console\Domain\Libs\ZnShell;
use ZnCore\Base\Libs\App\Enums\AppEventEnum;

/**
 * Автозапуск CRON-задач при каждом запросе.
 *
 * Конфигурация в dotEnv по имени "CRON_AUTORUN".
 * Значения: 0/1
 */
class AutorunQueueSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents(): array
    {
        return [
            AppEventEnum::AFTER_INIT_DISPATCHER => 'onAfterInit',
        ];
    }

    public function callbackWithShell() {
        $shell = new ZnShell();
        $process = $shell->getProcessFromCommandString('queue:run');
//            $process->run();
        $process->start();
        $process->disableOutput();
        while ($process->isRunning()) {}
    }

    public function callbackWithService() {
        /** @var JobServiceInterface $jobService */
            $jobService = ContainerHelper::getContainer()->get(JobServiceInterface::class);
            $jobService->touch();
    }

    public function onAfterInit(Event $event)
    {
        register_shutdown_function([$this, 'callbackWithService']);
    }
}
