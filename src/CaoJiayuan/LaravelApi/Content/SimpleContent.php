<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/22
 * Time: 下午6:19
 */

namespace CaoJiayuan\LaravelApi\Content;


class SimpleContent
{

    protected $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    public function __toString()
    {
        return $this->content;
    }

    public function toString()
    {
        return $this->content;
    }
}
