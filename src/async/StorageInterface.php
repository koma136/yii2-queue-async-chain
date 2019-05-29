<?php

namespace koma136\queue\async_chain;

use phpDocumentor\Reflection\Types\Integer;

/**
 * Interface StorageInterface
 * @package koma136\queue\async_chain
 */
interface StorageInterface
{
    /**
     * сохраняет список последовательно запускаемых задачь
     * @param array $jobs
     * @return mixed
     */
    public function add(array $jobs);

    /**
     * @param string $groupId
     * @return mixed
     */
    public function getNextJob(string $groupId);

    /**
     * @param int $jobId
     * @return mixed
     */
    public function setDoneJob(int $jobId);

    /**
     * @param int $jobId
     * @return mixed
     */
    public function setPushedJob(int $Id, int $jobId);

    /**
     * @param string $groupId
     * @return mixed
     */
    public function remove(string $groupId);

    /**
     * @param string $groupId
     * @return array
     */
    public function getGroupJobs(string $groupId);
}