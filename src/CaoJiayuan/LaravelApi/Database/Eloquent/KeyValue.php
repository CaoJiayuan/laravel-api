<?php
/**
 * Created by Cao Jiayuan.
 * Date: 17-5-5
 * Time: 下午3:37
 */

namespace CaoJiayuan\LaravelApi\Database\Eloquent;

use Illuminate\Support\Arr;

class KeyValue extends BaseEntity
{
    public $timestamps = false;

    public static $items = [];

    public static function getItem($key, $default = null)
    {
        if (!static::$items) {
            static::$items = static::getConvertedData();
        }
        if (is_array($key)) {
            $result = [];
            foreach ($key as $k => $def) {
                if (is_numeric($k)) {
                    $result[$def] = Arr::get(static::$items, $def);
                } else {
                    $result[$k] = Arr::get(static::$items, $k, $def);
                }
            }
            return $result;
        }
        return Arr::get(static::$items, $key, $default);
    }

    public static function getConvertedData()
    {
        $all = static::all();

        $data = [];
        foreach ($all as $item) {
            $data[$item->key] = $item->value;
        }

        return $data;
    }

    public static function store($key, $value = null)
    {
        $results = [];
        if (is_array($key)) {
            foreach ($key as $k => $item) {
                if (!is_numeric($k)) {
                    $results[] = self::put($k, $item);
                }
            }
        } else if (!is_numeric($key) && $value !== null) {
            $results[] = static::put($key, $value);
        }

        return $results;
    }

    public static function put($key, $value)
    {
        $attr = [
            'key'     => $key,
            'value'   => $value,
        ];

        return static::updateOrCreate([
            'key'     => $key,
        ], $attr);
    }
}
