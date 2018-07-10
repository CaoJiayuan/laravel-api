<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/15
 * Time: 下午4:24
 */

namespace CaoJiayuan\LaravelApi\Image;

use Closure;
use Intervention\Image\Image as InterventionImage;
use Intervention\Image\ImageManager as Intervention;
use Intervention\Image\ImageManagerStatic as InterventionStatic;

/**
 * Class Image
 * @package CaoJiayuan\LaravelApi\Image
 * @mixin InterventionImage
 */
class Image
{
    protected $path;

    /**
     * @var Intervention
     */
    protected $manager;
    /**
     * @var InterventionImage
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
        $result = call_user_func_array([$this->image, $name], $arguments);

        if ($result instanceof InterventionImage) {
            return $this;
        }
        return $result;
    }

    public static function make($path)
    {
        return new static($path);
    }

    public static function canvas($width, $height, $background = null)
    {
        return InterventionStatic::canvas($width, $height, $background);
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

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return InterventionImage
     */
    public function getImage()
    {
        return $this->image;
    }

    public function dominantColor($x = 0, $y = 0, $width = null, $height = null)
    {
        $width = $width ?: $this->getWidth();
        $height = $height ?: $this->getHeight();
        $rTotal = $bTotal = $gTotal = $aTotal = $total = 0;
        for ($i = $x; $i < $width + $x; $i++) {
            for ($j = $y; $j < $height + $y; $j++) {
                list($r, $g, $b, $a) = $this->pickColor($x, $y);
                $rTotal += $r;
                $gTotal += $g;
                $bTotal += $b;
                $aTotal += $a;
                $total++;
            }
        }
        $rAverage = round($rTotal / $total);
        $gAverage = round($gTotal / $total);
        $bAverage = round($bTotal / $total);
        $aAverage = round($aTotal / $total);

        return [$rAverage, $gAverage, $bAverage, $aAverage];
    }
}
