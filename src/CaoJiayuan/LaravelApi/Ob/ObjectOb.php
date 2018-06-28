<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/28
 * Time: 下午2:35
 */

namespace CaoJiayuan\LaravelApi\Ob;



use CaoJiayuan\LaravelApi\Foundation\Exceptions\BadPropertyAccessException;

abstract class ObjectOb extends Ob
{
    protected $chainCall = false;

    public function __call($name, $arguments)
    {
        $obj = $this->_value_()->getVal([$name, false]);

        if (method_exists($obj, $name)) {
            $result = call_user_func_array([$obj, $name], $arguments);

            if ($this->chainCall) {
                return $this;
            }
            return $result;
        }

        $method = get_class($obj) . ':' .$name;

        throw new \BadMethodCallException("Method not exists [$method]");
    }

    public function __get($name)
    {
        $obj = $this->_value_()->getVal([$name, false]);

        if (property_exists($obj, $name)) {
            return $obj->$name;
        }

        $prop = get_class($obj) . ':' .$name;

        throw new BadPropertyAccessException("Property not exists [$prop]");
    }
}
