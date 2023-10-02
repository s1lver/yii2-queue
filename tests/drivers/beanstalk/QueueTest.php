<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace tests\drivers\beanstalk;

use Pheanstalk\Exception\ServerException;
use Pheanstalk\Pheanstalk;
use tests\app\PriorityJob;
use tests\app\RetryJob;
use tests\drivers\CliTestCase;
use Yii;
use yii\queue\beanstalk\Queue;

/**
 * Beanstalk Queue Test.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class QueueTest extends CliTestCase
{
    public function testRun(): void
    {
        $job = $this->createSimpleJob();
        $this->getQueue()->push($job);
        $this->runProcess(['php', 'yii', 'queue/run']);

        $this->assertSimpleJobDone($job);
    }

    public function testStatus(): void
    {
        $job = $this->createSimpleJob();
        $id = $this->getQueue()->push($job);
        $isWaiting = $this->getQueue()->isWaiting($id);
        $this->runProcess(['php', 'yii', 'queue/run']);
        $isDone = $this->getQueue()->isDone($id);

        $this->assertTrue($isWaiting);
        $this->assertTrue($isDone);
    }

    public function testPriority(): void
    {
        $this->getQueue()->priority(100)->push(new PriorityJob(['number' => 1]));
        $this->getQueue()->priority(300)->push(new PriorityJob(['number' => 5]));
        $this->getQueue()->priority(200)->push(new PriorityJob(['number' => 3]));
        $this->getQueue()->priority(200)->push(new PriorityJob(['number' => 4]));
        $this->getQueue()->priority(100)->push(new PriorityJob(['number' => 2]));
        $this->runProcess(['php', 'yii', 'queue/run']);

        $this->assertEquals('12345', file_get_contents(PriorityJob::getFileName()));
    }

    public function testListen(): void
    {
        $this->startProcess(['php', 'yii', 'queue/listen', '1']);
        $job = $this->createSimpleJob();
        $this->getQueue()->push($job);

        $this->assertSimpleJobDone($job);
    }

    public function testLater(): void
    {
        $this->startProcess(['php', 'yii', 'queue/listen', '1']);
        $job = $this->createSimpleJob();
        $this->getQueue()->delay(2)->push($job);

        $this->assertSimpleJobLaterDone($job, 2);
    }

    public function testRetry(): void
    {
        $this->startProcess(['php', 'yii', 'queue/listen', '1']);
        $job = new RetryJob(['uid' => uniqid()]);
        $this->getQueue()->push($job);
        sleep(6);

        $this->assertFileExists($job->getFileName());
        $this->assertEquals('aa', file_get_contents($job->getFileName()));
    }

    public function testRemove(): void
    {
        $id = $this->getQueue()->push($this->createSimpleJob());
        $this->assertTrue($this->jobIsExists($id));
        $this->runProcess(['php', 'yii', 'queue/remove', $id]);

        $this->assertFalse($this->jobIsExists($id));
    }

    /**
     * @return Queue
     */
    protected function getQueue(): Queue
    {
        return Yii::$app->beanstalkQueue;
    }

    /**
     * @param int|string|null $id of a job
     * @return bool
     * @throws
     */
    protected function jobIsExists(int|string|null $id): bool
    {
        $connection = new Pheanstalk($this->getQueue()->host, $this->getQueue()->port);
        try {
            $connection->peek($id);
            return true;
        } catch (ServerException $e) {
            if (str_starts_with($e->getMessage(), 'NOT_FOUND')) {
                return false;
            }

            throw $e;
        }
    }
}
