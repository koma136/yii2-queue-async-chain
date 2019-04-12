<?php
namespace koma136\queue\async_chain;

use yii\queue\Queue;

/**
 * Class AsyncChainJobInterface
 * @package koma136\queue\async_chain
 */
interface AsyncChainJobInterface
{
    /**
     * Уникальный идентификатор группы заданий, по которому будет определяться сколько этих заданий
     * отправлено и сколько выполнено.
     * @return string
     */
    public function getGroupId();

    /**
     * Метод будет запущен после того, как выполнится вся группа заданий.
     * @param Queue $queue
     * @param array $results результаты выполненных заданий.
     */
    public function finalizeGroup($queue, array $results);

    /**
     * Порядковый номер в очереди последовательных задачь
     * @return integer
     */
    public function getIndex();

}