<?php
/**
 * Sessionless image captcha
 */

namespace CaoJiayuan\LaravelApi\Captcha;


use Gregwar\Captcha\CaptchaBuilder;

class ApiCaptcha extends SimpleCaptcha
{

    public static function meta($length = 6)
    {
        $value = str_random($length);

        return [
            'token' => app('encrypter')->encrypt($value)
        ];
    }

    public static function make($value, $encrypted = true)
    {
        $encrypted && $value = app('encrypter')->decrypt($value);

        return new static(new CaptchaBuilder($value));
    }
}
