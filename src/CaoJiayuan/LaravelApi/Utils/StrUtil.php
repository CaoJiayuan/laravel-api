<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/7/10
 * Time: 下午2:16
 */

namespace CaoJiayuan\LaravelApi\Utils;


class StrUtil
{

    public static function chunk($string, $chunkLength)
    {
        $chunks = [];
        $len = mb_strlen($string);
        $start = 0;
        while ($start < $len) {
            $chunks[] = mb_substr($string, $start, $chunkLength);

            $start += $chunkLength;
        }

        return $chunks;
    }

    public static function chunkSplit($string, $chunkLength, $endWith = PHP_EOL)
    {
        $chunks = self::chunk($string, $chunkLength);

        return implode($endWith, $chunks);
    }
}
