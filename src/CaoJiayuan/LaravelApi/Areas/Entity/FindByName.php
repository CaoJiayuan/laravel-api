<?php
/**
 * Created by PhpStorm.
 * User: caojiayuan
 * Date: 2018/7/28
 * Time: 下午3:49
 */

namespace CaoJiayuan\LaravelApi\Areas\Entity;


trait FindByName
{
    /**
     * @param $key
     * @return static
     */
    public static function findByNameOrId($key)
    {
        if (!is_numeric($key)) {
            return static::findByName($key);
        }

        return static::find($key);
    }

    /**
     * @param $name
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public static function findByName($name)
    {
        return static::where('name', 'like', "%$name%")->first();
    }

}