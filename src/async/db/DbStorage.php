<?php

namespace koma136\queue\async_chain\db;

use koma136\queue\async_chain\AsyncChainJobInterface;
use \koma136\queue\async_chain\StorageInterface;
use koma136\queue\chain\StatusHelper;
use yii\base\BaseObject;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\mutex\Mutex;
use yii\queue\serializers\PhpSerializer;
use yii\queue\serializers\SerializerInterface;

/**
 * Class DbStorage
 * @package koma136\queue\async_chain\db
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
    public $tableName = '{{%queue-async-chain}}';

    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::class);
        $this->serializer = Instance::ensure($this->serializer, SerializerInterface::class);
    }

    /**
     * @param array $jobs
     * @return mixed|void
     * @throws \yii\db\Exception
     */
    public function add(array $jobs)
    {
        /**
         * @var AsyncChainJobInterface $job
         */
        foreach ($jobs as $job){
            $this->db->createCommand()->insert($this->tableName, [
                'job' => $this->serializer->serialize($job),
                'group' => $job->getGroupId(),
                'status' => StatusHelper::NEW
            ])->execute();
        }
    }

    /**
     * @param string $groupId
     * @throws \yii\db\Exception
     */
    public function remove(string $groupId)
    {
        $this->db->createCommand()
            ->delete($this->tableName, ['group' => $groupId])
            ->execute();
    }

    /**
     * @param string $groupId
     * @return array|bool|mixed
     */
    public function getNextJob(string $groupId)
    {
        $row = (new Query())->from($this->tableName)->where(['group' => $groupId,'status'=>StatusHelper::NEW])->orderBy('id')->one($this->db);
        if(!$row){
            return false;
        }
        $row['job'] = $this->serializer->unserialize($row['job']);
        return $row;
    }

    /**
     * @param int $jobId
     * @param $result
     * @return mixed|void
     * @throws \yii\db\Exception
     */
    public function setDoneJob(int $jobId)
    {
        $this->db->createCommand()->update($this->tableName, [
            'status' => StatusHelper::DONE,
        ],[
            'job_id' => $jobId,
        ])->execute();
    }

    /**
     * @param int $Id
     * @param int $jobId
     * @return mixed|void
     * @throws \yii\db\Exception
     */
    public function setPushedJob(int $Id,int $jobId)
    {
        $this->db->createCommand()->update($this->tableName, [
            'status' => StatusHelper::PUSHED,
            'job_id' => $jobId
        ],[
            'id' => $Id,
        ])->execute();
    }


}