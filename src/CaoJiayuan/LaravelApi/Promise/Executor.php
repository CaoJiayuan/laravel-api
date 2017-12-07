<?php
/**
 * Created by Cao Jiayuan.
 * Date: 17-4-28
 * Time: 上午11:40
 */

namespace CaoJiayuan\LaravelApi\Promise;


abstract class Executor
{
    protected $promise;

    public function __construct($autoResolve = true)
    {
        $this->promise = promise(function () {
            return $this->resolve();
        })->then(function ($result, $next) {
            $this->onFulfilled($result);
            return $next($result);
        })->rejected(function ($ex) {
            $this->onRejected($ex);
        });
        if ($autoResolve) {
            $this->promise->resolveIfNotResolved();
        } else {
            $this->promise->fulfill();
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
}
