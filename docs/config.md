# Конфигурация

## Конфигурация миграций

Указываем папку с миграциями в конфиге `config/eloquent/main.yaml`:

```yaml
migrate:
    directory:
        - /vendor/php7lab/sandbox/src/Queue/Domain/Migrations
```

## Конфигурация консольной команды

```yaml
services:
    ZnBundle\Queue\Symfony4\Commands\RunCommand:
        tags: ['controller.service_arguments']
```
