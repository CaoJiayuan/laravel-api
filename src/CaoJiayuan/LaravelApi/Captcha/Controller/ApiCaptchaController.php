<?php
/**
 * Sessionless image captcha
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
            $captcha = ApiCaptcha::make($token);
        } catch (\Exception $exception) {
            throw new NotFoundHttpException();
        }

        return $captcha->render();
    }

    public function token()
    {
        return response()->json(ApiCaptcha::meta(6));
    }
}
