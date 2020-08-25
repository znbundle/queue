# Выполнение задач

Обычно, очередь выполняется по CRON.

Можно это сделать в коде так:

```php
/** @var \PhpBundle\Queue\Domain\Interfaces\Services\JobServiceInterface $jobService */

$jobService->runAll('email');
```

либо выполнить команду:

```
php bin/console queue:run
```
