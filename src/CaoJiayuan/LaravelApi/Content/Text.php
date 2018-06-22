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
}
