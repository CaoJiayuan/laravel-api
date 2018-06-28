<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/28
 * Time: 下午2:26
 */

namespace CaoJiayuan\LaravelApi\Ob;


class Value
{

    private $val;
    private $onSet;
    private $onGet;

    public function __construct($val)
    {
        $this->val = $val;
    }

    /**
     * @param null $param
     * @return mixed
     */
    public function getVal($param = null)
    {
        call_user_func_array($this->onGet, [$this->val, $param]);
        return $this->val;
    }

    /**
     * @param mixed $val
     * @return $this
     */
    public function setVal($val)
    {
        call_user_func_array($this->onSet, [$val, $this->val]);

        $this->val = $val;
        return $this;
    }

    public function onSet(callable $cb)
    {
        $this->onSet = $cb;

        return $this;
    }

    public function onGet(callable $cb)
    {
        $this->onGet = $cb;

        return $this;
    }
}
