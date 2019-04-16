<?php

namespace koma136\queue\chain\db;

use koma136\queue\StatusHelper;
use yii\base\BaseObject;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use koma136\queue\chain\StorageInterface;
use yii\queue\serializers\PhpSerializer;
use yii\queue\serializers\SerializerInterface;

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
     * @var int timeout
     */
    public $mutexTimeout = 3;
    /**
     * @var SerializerInterface|array
     */
    public $serializer = PhpSerializer::class;
    /**
     * @var string table name
     */
    public $tableName = '{{%queue-chain}}';

    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::class);
        $this->serializer = Instance::ensure($this->serializer, SerializerInterface::class);

    }

    /**
     * @param string $groupId
     * @param $job
     * @param mixed|null $result
     * @throws \yii\db\Exception
     */
    public function addDoneCount($groupId, $jobId, $result)
    {
        $this->db->createCommand()->update($this->tableName, [
            'status' => StatusHelper::DONE,
            'results' => $this->serializer->serialize($result)
        ],[
            'job_id' => $jobId,
            'group' => $groupId
            ])->execute();
    }

    /**
     * @param string $groupId
     * @param $job
     * @throws \yii\db\Exception
     */
    public function addPushedCount($groupId, $jobId)
    {
        $this->db->createCommand()->insert($this->tableName, [
            'job_id' => $jobId,
            'group' => $groupId,
            'status' => StatusHelper::PUSHED
        ])->execute();
    }

    /**
     * @param string $groupId
     * @return array|void
     */
    public function getProgress($groupId)
    {
        return [
            (int) (new Query())->from($this->tableName)->where(['group' => $groupId,'status'=>StatusHelper::DONE])->count('id',$this->db),
            (int) (new Query())->from($this->tableName)->where(['group' => $groupId])->count('id',$this->db)
        ];


    }

    /**
     * @param string $groupId
     * @return array
     * @throws \yii\db\Exception
     */
    public function reset($groupId)
    {
        $rezuils = [];
        $query = (new Query())->from($this->tableName)->where(['group' => $groupId,'status'=>StatusHelper::DONE]);
        foreach ($query->each(100,$this->db) as $row){
            $rezuils[] = $this->serializer->unserialize($row['results']);
        }
        $this->db->createCommand()
            ->delete($this->tableName, ['group' => $groupId])
            ->execute();
        return $rezuils;
    }
}