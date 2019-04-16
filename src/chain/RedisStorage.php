<?php

namespace koma136\queue\chain;

use yii\base\BaseObject;
use yii\di\Instance;
use yii\redis\Connection;

/**
 * Redis Storage
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class RedisStorage extends BaseObject implements StorageInterface
{
    /**
     * @var Connection|array|string
     */
    public $redis = 'redis';
    /**
     * @var string
     */
    public $prefix  = 'queue-chain';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->redis = Instance::ensure($this->redis, Connection::class);
    }

    /**
     * @inheritdoc
     */
    public function addPushedCount($groupId, $job)
    {
        $this->redis->incr("$this->prefix.$groupId.pushed");
    }

    /**
     * @inheritdoc
     */
    public function addDoneCount($groupId, $job, $result)
    {
        $this->redis->incr("$this->prefix.$groupId.done");
        if ($result !== null) {
            $this->redis->rpush("$this->prefix.$groupId.results", serialize($result));
        }
    }

    /**
     * @inheritdoc
     */
    public function getProgress($groupId)
    {
        return [
            (int) $this->redis->get("$this->prefix.$groupId.done"),
            (int) $this->redis->get("$this->prefix.$groupId.pushed"),
        ];
    }

    /**
     * @inheritdoc
     */
    public function reset($groupId)
    {
        $results = [];
        while (($result = $this->redis->lpop("$this->prefix.$groupId.results")) !== null) {
            $results[] = unserialize($result);
        }
        $this->redis->del(
            "$this->prefix.$groupId.results",
            "$this->prefix.$groupId.done",
            "$this->prefix.$groupId.pushed"
        );
        return $results;
    }
}
