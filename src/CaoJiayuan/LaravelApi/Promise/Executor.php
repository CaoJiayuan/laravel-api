<?php
/**
 * Created by Cao Jiayuan.
 * Date: 17-4-28
 * Time: 上午11:40
 */

namespace CaoJiayuan\LaravelApi\Promise;


use Illuminate\Contracts\Support\Jsonable;

abstract class Executor implements Jsonable
{
    protected $promise;

    public function __construct($resolveNow = false)
    {
        $this->promise = promise(function () {
            return $this->resolve();
        })->then(function ($result, $next) {
            $this->onFulfilled($result);
            return $next($result);
        })->rejected(function ($ex) {
            $this->onRejected($ex);
        });
        if ($resolveNow) {
            $this->promise->resolveIfNotResolved();
        }
    }

    abstract protected function onFulfilled($result);

    abstract protected function onRejected(\Exception $exception);

    abstract protected function resolve();

    public function execute()
    {
        $this->promise->pending();
        $this->promise->resolveIfNotResolved();
    }

    public function toJson($options = 0)
    {
        return $this->promise->toJson($options);
    }

    /**
     * @return Promise
     */
    public function getPromise()
    {
        return $this->promise;
    }
}
