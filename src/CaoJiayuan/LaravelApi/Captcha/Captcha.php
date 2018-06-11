<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/11
 * Time: 下午2:18
 */

namespace CaoJiayuan\LaravelApi\Captcha;


interface Captcha
{
    public function render($quality = 90);
}
