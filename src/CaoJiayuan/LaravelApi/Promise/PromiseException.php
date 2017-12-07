<?php
/**
 * Created by Cao Jiayuan.
 * Date: 17-4-28
 * Time: 上午10:11
 */

namespace CaoJiayuan\LaravelApi\Promise;

use Throwable;

class PromiseException extends \LogicException
{
    public function __construct($message, $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
