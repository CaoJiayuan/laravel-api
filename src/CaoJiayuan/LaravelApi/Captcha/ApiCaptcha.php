<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/11
 * Time: 下午2:19
 */

namespace CaoJiayuan\LaravelApi\Captcha;


class ApiCaptcha extends SimpleCaptcha
{

    public function meta($length = 6)
    {
        $value = str_random($length);

        return [
            'token' => app('encrypter')->encrypt($value)
        ];
    }
}
