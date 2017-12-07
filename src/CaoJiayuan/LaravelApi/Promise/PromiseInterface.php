<?php
/**
 * Created by Cao Jiayuan.
 * Date: 17-2-13
 * Time: 下午4:49
 */

namespace CaoJiayuan\LaravelApi\Promise;


interface PromiseInterface
{
    const PENDING = 0;
    const FULFILLED = 1;
    const REJECTED = 2;

    public function then(callable $onFulfilled);

    public function rejected(callable $onRejected);

}
