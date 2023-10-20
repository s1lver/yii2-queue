<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\queue\cli;

use yii\base\Event;

/**
 * Worker Event.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 * @since 2.0.2
 */
class WorkerEvent extends Event
{
    /**
     * @inheritdoc
     */
    public $name;
    /**
     * @var Queue
     * @inheritdoc
     * @psalm-suppress PropertyNotSetInConstructor, NonInvariantDocblockPropertyType
     */
    public $sender;
    /**
     * @var LoopInterface
     * @psalm-suppress PropertyNotSetInConstructor
     */
    public LoopInterface $loop;
    /**
     * @var null|int exit code
     */
    public ?int $exitCode = null;
}
