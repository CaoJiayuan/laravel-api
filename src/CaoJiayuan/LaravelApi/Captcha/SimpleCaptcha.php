<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/11
 * Time: 下午2:20
 */

namespace CaoJiayuan\LaravelApi\Captcha;


use Gregwar\Captcha\CaptchaBuilder;

class SimpleCaptcha implements Captcha
{
    /**
     * @var CaptchaBuilder
     */
    protected $provider;

    public function __construct(CaptchaBuilder $provider)
    {
        $this->provider = $provider;
    }

    public function render($quality = 90)
    {
        $this->rebuild();
        ob_start();
        $this->provider->output($quality);
        $content = ob_get_clean();
        return response($content, 200, [
            'Content-Type' => 'image/jpeg'
        ]);
    }

    public function rebuild($force = false)
    {
        if ($force || is_null($this->provider->getContents())) {
            $this->provider->build();
        }
    }
}
