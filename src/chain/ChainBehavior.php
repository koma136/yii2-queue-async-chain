<?php

namespace koma136\queue\chain;

use yii\di\Instance;
use yii\queue\ExecEvent;
use yii\queue\PushEvent;
use yii\queue\Queue;

/**
 * Chain Behavior
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class ChainBehavior extends \yii\base\Behavior
{
    /**
     * @var StorageInterface|array|string
     */
    public $storage;
    /**
     * @var Queue
     * @inheritdoc
     */
    public $owner;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->storage = Instance::ensure($this->storage, StorageInterface::class);
    }

    /**
     * Возвращает прогресс выполнения группы заданий
     * @param string $groupId
     * @return array
     */
    public function getGroupProgress($groupId)
    {
        return $this->storage->getProgress($groupId);
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            Queue::EVENT_AFTER_PUSH => 'afterPush',
            Queue::EVENT_AFTER_EXEC => 'afterExec',
            Queue::EVENT_AFTER_ERROR => 'afterError',
        ];
    }

    /**
     * @param PushEvent $event
     */
    public function afterPush(PushEvent $event)
    {
        if (!$event->job instanceof ChainJobInterface) {
            return;
        }
        $this->storage->addPushedCount($event->job->getGroupId(),$event->id);
    }

    /**
     * @param ExecEvent $event
     */
    public function afterExec(ExecEvent $event)
    {
        if (!$event->job instanceof ChainJobInterface) {
            return;
        }
        $this->registerResult($event);
    }

    /**
     * @param ExecEvent $event
     */
    public function afterError(ExecEvent $event)
    {
        if (!$event->job instanceof ChainJobInterface) {
            return;
        }
        if ($event->retry) {
            return;
        }
        $this->registerResult($event);
    }

    /**
     * @param ChainJobInterface $job
     * @param mixed $result
     */
    protected function registerResult(ExecEvent $event)
    {
        $groupId = $event->job->getGroupId();
        $this->storage->addDoneCount($groupId, $event->id, $event->result);
        var_dump($this->storage->getProgress($groupId));
        list($pos, $size) = $this->storage->getProgress($groupId);
        if ($size > 0 && $pos == $size) {
            $results = $this->storage->reset($groupId);
            $event->job->finalizeGroup($this, $results);
        }
    }
}
