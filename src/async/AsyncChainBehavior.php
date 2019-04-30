<?php

namespace koma136\queue\async_chain;

use yii\base\Security;
use yii\di\Instance;
use yii\queue\ExecEvent;
use yii\queue\Queue;

/**
 * Class AsyncChainBehavior
 * @package koma136\queue\async_chain
 */
class AsyncChainBehavior extends \yii\base\Behavior
{
    /**
     * @var StorageInterface|array|string
     */
    public $storage;
    /**
     * @var Security
     */
    public $security;
    /**
     * @var integer $delay
     */
    public $delay;
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->storage = Instance::ensure($this->storage, StorageInterface::class);
        $this->security = \Yii::$app->security;
    }

    /**
     * @return array
     */
    public function events()
    {
        return [
            Queue::EVENT_AFTER_EXEC => 'afterExec',
            Queue::EVENT_AFTER_ERROR => 'afterError',
        ];
    }

    /**
     * @param ExecEvent $event
     */
    public function afterExec(ExecEvent $event)
    {
        if (!$event->job instanceof AsyncChainJobInterface) {
            return;
        }
        $this->storage->setDoneJob($event->id);
        if(!$this->pushNextJob($event->job->getGroupId(),$event->result)){
            $this->storage->remove($event->job->getGroupId());
            $event->job->finalizeGroup($event);
        }
    }

    /**
     * @param ExecEvent $event
     */
    public function afterError(ExecEvent $event)
    {
        if (!$event->job instanceof AsyncChainJobInterface) {
            return;
        }
        if ($event->retry) {
            return;
        }
        $this->storage->remove($event->job->getGroupId());
        $event->job->finalizeGroup($event);
    }

    /**
     * @param array $jobs
     * @param string|null $groupId
     * @return string
     * @throws \yii\base\Exception
     */
    public function async(array $jobs,string $groupId = null){
        if(!$groupId) {
            $groupId = $this->security->generateRandomString();
        }
        foreach ($jobs as $key=>$job){
            if(!$job instanceof AsyncChainJobInterface){
                unset($jobs[$key]);
            }else{
                $job->setGroupId($groupId);
            }
        }
        $this->storage->add($jobs);
        $this->pushNextJob($groupId, null);
        return $groupId;
    }

    /**
     * @param $groupId
     * @param $rezult
     * @return bool
     */
    public function pushNextJob($groupId, $rezult){
        $row = $this->storage->getNextJob($groupId);
        if(!$row) return false;
        $row['job']->setRezultPrevJob($rezult);
        $jobId = $this->owner->delay($this->delay)->push($row['job']);
        $this->storage->setPushedJob($row['id'],$jobId);
        return $jobId;
    }
}