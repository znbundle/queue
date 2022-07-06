<?php

namespace ZnBundle\Queue\Domain\Enums\Rbac;

use ZnCore\Enum\Interfaces\GetLabelsInterface;
use ZnCore\Contract\Rbac\Interfaces\GetRbacInheritanceInterface;

class QueueSchedulePermissionEnum implements GetLabelsInterface, GetRbacInheritanceInterface
{

    public const CRUD = 'oQueueScheduleCrud';

    public const ALL = 'oQueueScheduleAll';

    public const ONE = 'oQueueScheduleOne';

    public const CREATE = 'oQueueScheduleCreate';

    public const UPDATE = 'oQueueScheduleUpdate';

    public const DELETE = 'oQueueScheduleDelete';

    public const RESTORE = 'oQueueScheduleRestore';

    public static function getLabels()
    {
        return [
            self::CRUD => 'QueueSchedule. Полный доступ',
            self::ALL => 'QueueSchedule. Просмотр списка',
            self::ONE => 'QueueSchedule. Просмотр записи',
            self::CREATE => 'QueueSchedule. Создание',
            self::UPDATE => 'QueueSchedule. Редактирование',
            self::DELETE => 'QueueSchedule. Удаление',
            self::RESTORE => 'QueueSchedule. Восстановление',
        ];
    }

    public static function getInheritance()
    {
        return [
            self::CRUD => [
                self::ALL,
                self::ONE,
                self::CREATE,
                self::UPDATE,
                self::DELETE,
                self::RESTORE,
            ],
        ];
    }


}

