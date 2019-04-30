# yii2-queue-async-chain
Async chain for Yii2 Queue

Синхронные и асинхронные очереди 
Настройка
---------

 ```php
 <?php
 return [
    'components' => [
        'queue' => [
           'class' => \yii\queue\db\Queue::class,
            'mutex' => \yii\mutex\MysqlMutex::class, // Mutex used to sync queries
            'as chain' => [
                'class' => \koma136\queue\chain\ChainBehavior::class,
                'storage' => \koma136\queue\chain\db\DbStorage::class
            ],
            'as async' => [
                'class' => \koma136\queue\async_chain\AsyncChainBehavior::class,
                'storage' => \koma136\queue\async_chain\db\DbStorage::class,
            ],
        ],
    ],
 ];
 ```
 Использование
-------------

Пример синхронного группового задания:
спасибо https://github.com/zhuravljov

```php
class MyGroupJob extends BaseObject implements JobInterface, ChainJobInterface
{
    /**
     * @return string уникальный идентификатор группы заданий.
     * @inheritdoc ChainJobInterface
     */
    public function getGroupId()
    {
        return 'group-123';
    }
    
    /**
     * @inheritdoc JobInterface
     */
    public function execute($queue)
    {
        //...
        return 12345;
    }
    
    /**
     * Финализация запустится один раз после того, как выполнятся все задания группы.
     * @inheritdoc ChainJobInterface
     */
    public function finalizeGroup($queue, array $results)
    {
        $queue->push(new MyFinalizeJob(['results' => $results]));
    }
}
```

Пример асинхронного группового задания
```
class TestChainJob extends AsyncChainBaseJob implements JobInterface,AsyncChainJobInterface
{
    /**
     * @var string
     */
    public $jobname;

    /**
     * @param \yii\queue\Queue $queue
     * @param array $results
     */
        public function finalizeGroup(ExecEvent $event)
        {
            echo "finish group ";
        }

    /**
     * @param \yii\queue\Queue $queue
     * @return mixed|void
     */
        public function execute($queue)
        {
            return $this->rezultPrevJob . '-test';
        }


}
```

запуск асинхронной цепочки задачь 
```
 $jobs = [
                new TestChainJob(["jobname"=>'1']),
                new TestChainJob(["jobname"=>'2']),
                new TestChainJob(["jobname"=>'3']),
            ];
            
Yii::$app->queue->async($jobs);
```
