<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/11
 * Time: 下午3:15
 */

namespace CaoJiayuan\LaravelApi\Captcha\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ApiCaptchaMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->get('captcha_token');
        $code = $request->get('captcha_code');
        $pass = false;
        try{
           $de =  app('encrypter')->decrypt($token);
           if (strtolower($de) == strtolower($code)) {
               $pass = true;
           }
        } catch (\Exception $exception) {}
        if (!$pass) {
            throw new UnprocessableEntityHttpException(trans('errors.captchaError') ?: 'Captcha error');
        }

        return $next($request);
    }
}
