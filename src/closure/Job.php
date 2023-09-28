<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\queue\closure;

use Laravel\SerializableClosure\Serializers\Native;
use yii\queue\JobInterface;

/**
 * Closure Job.
 *
 * @author Roman Zhuravlev <zhuravljov@gmail.com>
 */
class Job implements JobInterface
{
    /**
     * @var string serialized closure
     */
    public string $serialized;

    /**
     * Unserializes and executes a closure.
     * @inheritdoc
     */
    public function execute($queue)
    {
        $closure = unserialize($this->serialized)->getClosure();
        $nativeClosure = $closure();

        if ($nativeClosure instanceof Native) {
            return $nativeClosure();
        }

        return $nativeClosure->execute($queue);
    }
}
