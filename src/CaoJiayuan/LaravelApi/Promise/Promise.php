<?php
/**
 * Created by Cao Jiayuan.
 * Date: 17-4-28
 * Time: 上午9:30
 */

namespace CaoJiayuan\LaravelApi\Promise;


use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Pipeline\Pipeline;

class Promise implements PromiseInterface, Jsonable
{

    /**
     * @var callable
     */
    protected $executor;
    protected $onFulfilled = [];
    protected $onRejected;
    protected $status;
    /**
     * @var array
     */
    protected $params;
    protected $id;


    public function __construct(callable $executor, $params = [])
    {
        $this->id = $this->generateId();
        $this->executor = $this->useAsCallable($executor);
        $this->params = $params;
        /**
         * @param \Exception $ex
         */
        $this->onRejected = function ($ex) {
            $exClass = get_class($ex);
            throw new PromiseException("Unhandled exception [$exClass]", 500, $ex);
        };

        $this->status = static::PENDING;
    }

    public function generateId()
    {
        return md5(microtime(true) . uniqid());
    }

    public function useAsCallable($executor)
    {
        if (!is_callable($executor)) {
            return function () use ($executor) {
                return $executor;
            };
        }
        return $executor;
    }

    public function then(callable $onFulfilled)
    {
        array_push($this->onFulfilled, $onFulfilled);
//        $this->onFulfilled = $onFulfilled;
        return $this;
    }

    public function rejected(callable $onRejected)
    {
        $onRejected || $onRejected = function ($ex) {
            $exClass = get_class($ex);

            throw new PromiseException("Unhandled exception [$exClass]", 500, $ex);
        };
        $this->onRejected = $onRejected;
        return $this;
    }

    public static function resolve(callable $executor, $params = [])
    {
        return new static($executor, $params);
    }

    public function resolveIfNotResolved()
    {
        if ($this->status == static::PENDING) {
            $pipe = new Pipeline(app());
            try {
                ob_start();
                $paramArr = reset($this->params) ?: [];

                $pass = [];
                foreach ((array)$paramArr as $item) {
                    if (is_callable($item)) {
                        $pass[] = call_user_func($item);
                    } else {
                        $pass[] = $item;
                    }
                }
                $result = call_user_func_array($this->executor, $pass);
                $pipe->send($result)
                    ->through($this->onFulfilled)
                    ->then(function ($result) {
                        return $result;
                    });
            } catch (\Exception $exception) {
                ob_end_clean();
                $this->status = static::REJECTED;
                call_user_func($this->onRejected, $exception);
            }
            $echo = ob_get_clean();
            if ($echo) {
                echo $echo;
            }
            $this->status = static::FULFILLED;
        }
        return $this;
    }

    function __destruct()
    {
        $this->resolveIfNotResolved();
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function pending()
    {
        $this->status = static::PENDING;
    }

    public function fulfill()
    {
        $this->status = static::FULFILLED;
    }

    public function reject()
    {
        $this->status = static::REJECTED;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode([
            'id'     => $this->id,
            'status' => $this->status
        ], $options);
    }
}
