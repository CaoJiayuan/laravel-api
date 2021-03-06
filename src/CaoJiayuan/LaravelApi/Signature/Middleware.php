<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/7/27
 * Time: 上午11:41
 */

namespace CaoJiayuan\LaravelApi\Signature;


use CaoJiayuan\LaravelApi\Signature\Exceptions\UnsignedRequestException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Middleware
{
    /**
     * @var Validator
     */
    private $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $t = $request->header('X-Timestamp') ?: time();
        $s = $request->header('X-Signature');
        $interval = config('api-util.signature.interval', 5);
        if ((abs(intval($t) - time()) < $interval) && $this->validator->validate($s, $t)) {
            return $next($request);
        }

        return $this->responseUnsigned($request, $s, $t);
    }

    /**
     * @param Request $request
     * @param $s
     * @param $timestamp
     * @return Response|mixed
     */
    protected function responseUnsigned($request, $s, $timestamp)
    {
        throw new UnsignedRequestException();
    }
}
