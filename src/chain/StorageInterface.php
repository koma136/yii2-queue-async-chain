<?php

namespace koma136\queue\chain;

/**
 * Chain Storage Interface
 *
 * Интерфейс хранилища данных о выполнении групп заданий.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
interface StorageInterface
{
    /**
     * Метод должен увеличивать на единицу счетчик поставленных в очередь заданий в рамках
     * конкретной группы.
     * @param string $groupId
     */
    public function addPushedCount($groupId, $jobId);

    /**
     * Метод должен увеличивать счетчик выполненных заданий в рамках группы.
     * @param string $groupId
     * @param null|mixed $result результат выполнения задания.
     */
    public function addDoneCount($groupId, $jobId, $result);

    /**
     * Метод должен вырнуть прогресс выполнения группы заданий.
     * @param string $groupId
     * @return array
     */
    public function getProgress($groupId);

    /**
     * Сброс данных группы в хранилище.
     * @param string $groupId
     * @return array массив результатов выполнения всей группы заданий
     */
    public function reset($groupId);
}
