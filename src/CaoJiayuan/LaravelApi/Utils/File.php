<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/7/3
 * Time: 上午11:41
 */

namespace CaoJiayuan\LaravelApi\Utils;


use Illuminate\Support\Facades\Storage;

class File
{

    public static function remember($path, $expireMinutes, callable $callback, $disk = null)
    {
        $disk = self::getDisk($disk);
        if ($disk->exists($path)){
            if ($expireMinutes != INF) {
                if ($disk->lastModified($path) > time() - $expireMinutes * 60) {
                    return $path;
                }
            } else {
                return $path;
            }
        }
        call_user_func_array($callback, [$disk, $path]);

        return $path;
    }

    public static function downloadResponse($path, $name = null)
    {
        return response()->download($path, $name);
    }

    public static function getDisk($disk = null)
    {
        return Storage::disk($disk);
    }
}
