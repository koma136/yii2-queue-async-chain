<?php
namespace koma136\queue\async_chain;

use yii\queue\ExecEvent;
use yii\queue\Queue;

/**
 * Class AsyncChainJobInterface
 * @package koma136\queue\async_chain
 */
interface AsyncChainJobInterface
{

    /**
     * Метод будет запущен после того, как выполнится вся группа заданий.
     * @param Queue $queue
     * @param array $results результаты выполненных заданий.
     * @param $error
     */
    public function finalizeGroup(ExecEvent $event);

    /**
     * Устанавливает уникальный идентификатор для группы заданий
     * @param string $groupId
     * @return mixed
     */
    public function setGroupId(string $groupId);

    /**
     * @return string
     */
    public function getGroupId();

    /**
     * @param $rezult
     * @return mixed
     */
    public function setRezultPrevJob($rezult);
}