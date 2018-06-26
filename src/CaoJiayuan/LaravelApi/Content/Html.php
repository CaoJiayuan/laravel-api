<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/26
 * Time: 下午2:34
 */

namespace CaoJiayuan\LaravelApi\Content;


class Html extends Text
{

    public function getType(): string
    {
        return 'text/html';
    }
}
