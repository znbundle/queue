<?php


use Illuminate\Container\Container;
use Symfony\Component\Console\Application;
use ZnLib\Console\Symfony4\Helpers\CommandHelper;

/**
 * @var Application $application
 * @var Container $container
 */

$container->bind(\ZnBundle\Queue\Domain\Interfaces\Services\JobServiceInterface::class, \ZnBundle\Queue\Domain\Services\JobService::class);
$container->bind(\ZnBundle\Notify\Domain\Interfaces\Repositories\EmailRepositoryInterface::class, \ZnBundle\Notify\Domain\Repositories\Dev\EmailRepository::class);
$container->bind(\ZnBundle\Notify\Domain\Interfaces\Repositories\SmsRepositoryInterface::class, \ZnBundle\Notify\Domain\Repositories\Dev\SmsRepository::class);
$container->bind(\ZnBundle\Queue\Domain\Interfaces\Repositories\JobRepositoryInterface::class, \ZnBundle\Queue\Domain\Repositories\Eloquent\JobRepository::class);
$container->bind(\Psr\Container\ContainerInterface::class, function () {
    return Container::getInstance();
}, true);

CommandHelper::registerFromNamespaceList([
    'ZnBundle\Queue\Symfony4\Commands',
], $container);
