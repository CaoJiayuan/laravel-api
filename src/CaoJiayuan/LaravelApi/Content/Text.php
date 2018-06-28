<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/22
 * Time: 下午6:19
 */

namespace CaoJiayuan\LaravelApi\Content;


class Text extends SimpleContent
{
    public function trim()
    {
        $this->content = trim($this->content);

        return $this;
    }

    public function replace($find, $replace, $regex = false)
    {
        if ($regex) {
            $this->content = preg_replace($find, $replace, $this->content);
        } else {
            $this->content = str_replace($find, $replace, $this->content);
        }

        return $this;
    }

    public function sub($start, $length = null)
    {
        $this->content = substr($this->content, $start, $length);

        return $this;
    }

    public function match($regex, $all = false)
    {
        $match = [];

        if ($all) {
            preg_match_all($regex, $this->content, $match);
        } else {
            preg_match($regex, $this->content, $match);
        }

        return $match;
    }

    public function indexOf($needle)
    {
        return strpos($needle, $this->content);
    }

    public function contains($needles)
    {
        return str_contains($this->content, $needles);
    }
}
