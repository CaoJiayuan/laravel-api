<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/28
 * Time: 下午3:35
 */

namespace CaoJiayuan\LaravelApi\Helpers;


use CaoJiayuan\LaravelApi\Foundation\Exceptions\BadPropertyAccessException;

class Mixer
{

    /**
     * @var array
     */
    private $objects;

    public function __construct(...$objects)
    {
        $this->objects = $objects;
    }

    public function __call($name, $arguments)
    {
        return $this->callMethod($name, $arguments);
    }

    public function __get($name)
    {
       return $this->accessProperty($name);
    }

    public function callMethod($method, $arguments)
    {
        foreach($this->objects as $object) {
            try {
               $result = call_user_func_array([$object, $method], $arguments);
            } catch (\Exception $exception){
                continue;
            }
            return $result;
        }

        throw new \BadMethodCallException("Method not exists [$method]");
    }

    public function accessProperty($name)
    {
        foreach($this->objects as $object) {
            try {
                $result = $object->$name;
            } catch (\Exception $exception){
                continue;
            }
            return $result;
        }

        throw new BadPropertyAccessException("Property not exists [$name]");
    }
}
