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
        $this->storage->push($jobs);

        $this->pushNextJob($groupId, null);
    }

    /**
     * @param $groupId
     */
    public function pushNextJob($groupId, $rezult){
        $row = $this->storage->getNextJob($groupId);
        if(!$row) return false;
        $row['job']->setRezultPrevJob($rezult);
        $jobId = $this->push($row['job']);
        $this->storage->setPushedJob($row['id'],$jobId);
    }
}