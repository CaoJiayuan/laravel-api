<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/15
 * Time: 下午4:43
 */

namespace CaoJiayuan\LaravelApi\Image;


use Intervention\Image\ImageManager;

class Merger
{
    const MODE_MIN = 0;
    const MODE_MAX = 1;

    const DIRECTION_VERTICAL = 0;
    const DIRECTION_HORIZON = 1;


    /**
     * @var int
     */
    protected $columns;
    protected $manager;
    protected $direction;

    public function __construct($columns = 1, $direction = Merger::DIRECTION_VERTICAL)
    {
        $this->columns = $columns;
        $this->manager = new ImageManager();
        $this->direction = $direction;
    }

    public function merge($images, $mode = Merger::MODE_MIN)
    {
        if ($this->direction == static::DIRECTION_VERTICAL) {

        }
    }

    /**
     * @param Image[] $images
     * @param int $mode
     */
    public function mergeVertical($images, $mode = Merger::MODE_MIN)
    {
        /** @var Image[] $images */
        $images = $this->formatImages($images);

        $widths = array_map(function ($img) {
            /** @var Image $img */
            return $img->getHeight();
        }, $images);

        $width = $mode == Merger::MODE_MIN ? min($widths) : max($widths);

        $height = 0;

        foreach($images as $image) {
            $height += $image->getHeight() * ($width / $image->getWidth());
        }

        $canvas = $this->manager->canvas($width, $height);


    }

    protected function formatImages($images)
    {
        if (is_array($images)) {
            return array_map(function ($img) {
                return $this->formatImage($img);
            }, $images);
        } else {
            return $this->formatImage($images);
        }
    }

    protected function formatImage($img)
    {
        if (!$img instanceof Image) {
            return Image::make($img);
        }

        return $img;
    }
}
