<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/28
 * Time: 下午2:29
 */

namespace CaoJiayuan\LaravelApi\Ob;


abstract class Ob
{
    /**
     * @var Value
     */
    private $value;
    private $used = false;
    private $changed = false;

    public function __construct(Value $value)
    {
        $this->value = $value;
        $this->value->onSet(function ($now ,$old) {
            $this->_watch_($now, $old);
            $this->changed = true;
        });
        $this->value->onGet(function ($val, $param) {

            $this->_reading_($val, $param);
            $this->used = true;
        });
    }

    abstract protected function _watch_($now, $old);
    abstract protected function _reading_($value, $param);

    /**
     * @return Value
     */
    public function _value_(): Value
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function _using_(): bool
    {
        return $this->used;
    }

    /**
     * @return bool
     */
    public function _changed_(): bool
    {
        return $this->changed;
    }

}
