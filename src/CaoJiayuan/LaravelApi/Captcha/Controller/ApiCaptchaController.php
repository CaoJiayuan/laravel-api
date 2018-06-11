<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/11
 * Time: 下午3:03
 */

namespace CaoJiayuan\LaravelApi\Captcha\Controller;


use CaoJiayuan\LaravelApi\Captcha\ApiCaptcha;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiCaptchaController
{

    public function render(Request $request)
    {
        $token = $request->get('token');
        try{
           $value =  app('encrypter')->decrypt($token);
        } catch (\Exception $exception) {
            throw new NotFoundHttpException();
        }
        $captcha = new ApiCaptcha(new CaptchaBuilder($value));

        return $captcha->render();
    }

    public function token()
    {
        $value = str_random(6);

        return response()->json([
            'token' => app('encrypter')->encrypt($value)
        ]);
    }
}
