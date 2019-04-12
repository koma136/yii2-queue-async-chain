<?php

namespace koma136\queue\chain\db\migrations;

use yii\db\Migration;

/**
 * Class m190412_165143_QueueChain
 * @package yii\queue\chain\db\migrations
 */
class m190412_165143_QueueChain extends Migration
{
    public $tableName = '{{%queue-chain}}';
    public $tableOptions;


    public function up()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'job' => $this->binary()->notNull(),
            'group' => $this->string()->notNull(),
            'status' => $this->integer(),
            'results' => $this->binary()
        ], $this->tableOptions);

        $this->createIndex('group', $this->tableName, 'group');
    }

    public function down()
    {
        $this->dropTable($this->tableName);
    }
}
