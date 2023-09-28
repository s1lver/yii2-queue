<?php

declare(strict_types=1);

namespace tests\closure;

use Closure;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * Closure Job.
 *
 * @author Yetopen S.r.l. <info@yetopen.com>
 */
class ClosureJob extends BaseObject implements JobInterface
{
    /**
     * @var Closure
     */
    public $closure;

    public function execute($queue)
    {
        call_user_func($this->closure);
    }
}
