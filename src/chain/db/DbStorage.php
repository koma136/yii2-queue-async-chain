<?php

namespace koma136\queue\chain\db;

use yii\base\BaseObject;
use yii\db\Connection;
use yii\di\Instance;
use yii\mutex\Mutex;
use koma136\queue\chain\StorageInterface;

/**
 * Class DbStorage
 * @package yii\queue\chain
 */
class DbStorage extends BaseObject implements StorageInterface
{
    /**
     * @var Connection|array|string
     */
    public $db = 'db';
    /**
     * @var Mutex|array|string
     */
    public $mutex = 'mutex';
    /**
     * @var int timeout
     */
    public $mutexTimeout = 3;
    /**
     * @var string table name
     */
    public $tableName = '{{%queue-chain}}';

    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::class);
        $this->mutex = Instance::ensure($this->mutex, Mutex::class);
    }

    public function addDoneCount($groupId, $result)
    {
        // TODO: Implement addDoneCount() method.
    }
    public function addPushedCount($groupId)
    {
        // TODO: Implement addPushedCount() method.
    }
    public function getProgress($groupId)
    {
        // TODO: Implement getProgress() method.
    }
    public function reset($groupId)
    {
        // TODO: Implement reset() method.
    }
}