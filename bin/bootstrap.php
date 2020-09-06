<?php


use Illuminate\Container\Container;
use Symfony\Component\Console\Application;
use PhpLab\Core\Console\Helpers\CommandHelper;

/**
 * @var Application $application
 * @var Container $container
 */

$container = Container::getInstance();

$container->bind(Application::class, Application::class, true);

$container->bind(\PhpBundle\Queue\Domain\Interfaces\Services\JobServiceInterface::class, \PhpBundle\Queue\Domain\Services\JobService::class);
$container->bind(\PhpBundle\Notify\Domain\Interfaces\Repositories\EmailRepositoryInterface::class, \PhpBundle\Notify\Domain\Repositories\Dev\EmailRepository::class);
$container->bind(\PhpBundle\Notify\Domain\Interfaces\Repositories\SmsRepositoryInterface::class, \PhpBundle\Notify\Domain\Repositories\Dev\SmsRepository::class);
$container->bind(\PhpBundle\Queue\Domain\Interfaces\Repositories\JobRepositoryInterface::class, \PhpBundle\Queue\Domain\Repositories\Eloquent\JobRepository::class);
$container->bind(\Psr\Container\ContainerInterface::class, function () {
    return Container::getInstance();
}, true);

CommandHelper::registerFromNamespaceList([
    'PhpBundle\Queue\Symfony\Commands',
], $container);
