<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/7/19
 * Time: 下午2:36
 */

namespace CaoJiayuan\LaravelApi\Mock\Provider;


use Faker\Provider\Base;

class Image extends Base
{
    protected $baseUrl = 'https://picsum.photos/';

    public function image($width = 640, $height = 480, $gray = false, $seed = null)
    {
        $ps = [];
        if ($gray) {
            $ps[] = 'g';
        }
        $ps[] = $width;
        $ps[] = $height;
        $query = '';
        if ($seed) {
            $query = '?random=' . $seed;
        }

        return $this->baseUrl . implode('/', $ps) . $query;
    }

    public function imageUrl($width = 640, $height = 480, $gray = false, $seed = null)
    {
        return $this->image($width, $height, $gray, $seed);
    }
}
