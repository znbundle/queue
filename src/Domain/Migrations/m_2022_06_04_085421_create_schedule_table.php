<?php

namespace Migrations;

use Illuminate\Database\Schema\Blueprint;
use ZnDatabase\Migration\Domain\Base\BaseCreateTableMigration;

class m_2022_06_04_085421_create_schedule_table extends BaseCreateTableMigration
{

    protected $tableName = 'queue_schedule';
    protected $tableComment = 'Расписание выполнения регулярных задач';

    public function tableStructure(Blueprint $table): void
    {
        $table->integer('id')->autoIncrement()->comment('Идентификатор');
        $table->string('channel')->nullable()->comment('Имя канала потока обработки');
        $table->string('expression')->comment('Выражение расписания (совместимо с обычным CRON)');
        $table->string('class')->comment('Имя класса');
        $table->text('data')->nullable()->comment('Данные для задачи');
        $table->smallInteger('status_id')->comment('Статус');
        $table->dateTime('executed_at')->nullable()->comment('Время последнего выполнения');
        $table->dateTime('created_at')->comment('Время создания');
        $table->dateTime('updated_at')->nullable()->comment('Время обновления');
    }
}
