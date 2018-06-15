<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/15
 * Time: 下午4:24
 */

namespace CaoJiayuan\LaravelApi\Image;

use Closure;
use Intervention\Image\ImageManager as Intervention;

/**
 * Class Image
 * @package CaoJiayuan\LaravelApi\Image
 * @mixin \Intervention\Image\Image
 */
class Image
{
    protected $path;

    /**
     * @var Intervention
     */
    protected $manager;
    /**
     * @var \Intervention\Image\Image
     */
    protected $image;

    public function __construct($path)
    {
        $this->path = $path;
        $this->manager = new Intervention();
        $this->image = $this->manager->make($path);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->image, $name], $arguments);
    }

    public static function make($path)
    {
        return new static($path);
    }

    public function zoom($rate, Closure $cb = null)
    {
        list($w, $h) = $this->getDimension();

        return $this->resize($w * $rate, $h * $rate, $cb);
    }

    public function zoomByWidth($width, Closure $cb = null)
    {
        $rate = $width / $this->getWidth();

        return $this->zoom($rate, $cb);
    }

    public function zoomByHeight($height, Closure $cb = null)
    {
        $rate = $height / $this->getHeight();

        return $this->zoom($rate, $cb);
    }


    public function getDimension()
    {
        return [$this->getWidth(), $this->getHeight()];
    }
}
