<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/28
 * Time: 下午2:46
 */

namespace CaoJiayuan\LaravelApi\Html\Loader;


class ContentLoader implements Loader
{
    /**
     * @var
     */
    private $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    public function load()
    {
        return $this->content;
    }
}
