<?php

namespace Migrations;

use Illuminate\Database\Schema\Blueprint;
use ZnLib\Components\Status\Enums\StatusEnum;
use ZnDatabase\Migration\Domain\Base\BaseCreateTableMigration;
use ZnBundle\Queue\Domain\Enums\PriorityEnum;

class m_2019_12_27_100000_create_queue_job_table extends BaseCreateTableMigration
{

    protected $tableName = 'queue_job';
    protected $tableComment = 'Очередь задач';

    public function tableSchema()
    {
        return function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->comment('Идентификатор');
            $table->string('channel')->comment('Имя канала потока обработки');
            $table->string('class')->comment('Имя класса');
            $table->text('data')->nullable()->comment('Данные для задачи');
            $table->integer('priority')->default(PriorityEnum::NORMAL)->comment('Приоритет выполнения');
            $table->integer('delay')->default(0)->comment('Допустимая задержка');
            $table->integer('attempt')->default(0)->comment('Номер попытки выполнения');
            $table->smallInteger('status')->default(StatusEnum::ENABLED)->comment('Статус');
            $table->dateTime('pushed_at')->comment('Время создания');
            $table->dateTime('reserved_at')->nullable()->comment('Время резервирования задачи для выполнения');
            $table->dateTime('done_at')->nullable()->comment('Время завершения выполнения задачи');

            $table->index(['channel']);
            $table->index(['priority']);
            $table->index(['reserved_at']);
        };
    }

}
