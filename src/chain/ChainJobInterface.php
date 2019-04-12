<?php

namespace koma136\queue\chain;

use yii\queue\Queue;

/**
 * Chain Job Group Interface
 */
interface ChainJobInterface
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
}
