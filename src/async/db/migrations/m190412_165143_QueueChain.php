<?php

namespace koma136\queue\async_chain\db\migrations;

use yii\db\Migration;

/**
 * Class m190412_165143_QueueChain
 * @package yii\queue\chain\db\migrations
 */
class m190412_165143_QueueAsyncChain extends Migration
{
    public $tableName = '{{%queue-async-chain}}';
    public $tableOptions;


    public function up()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'job' => $this->binary()->notNull(),
            'job_id' => $this->integer(),
            'group' => $this->string()->notNull(),
            'status' => $this->integer()
        ], $this->tableOptions);

        $this->createIndex('group', $this->tableName, 'group');
    }

    public function down()
    {
        $this->dropTable($this->tableName);
    }
}
