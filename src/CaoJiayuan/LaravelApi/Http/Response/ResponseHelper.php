<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/3/28
 * Time: 下午5:17
 */

namespace CaoJiayuan\LaravelApi\Http\Response;


use CaoJiayuan\LaravelApi\Foundation\Exceptions\CustomHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait ResponseHelper
{
    public function respondMessage($status, $message)
    {
        throw new HttpException($status, $message);
    }

    public function respondCustomMessage($code, $message, $statusCode = 200, $data = [])
    {
        throw new CustomHttpException($code, $message, $data, $statusCode);
    }

    public function respondSuccess($message = 'Success')
    {
        $this->respondMessage(200, $message);
    }

    public function respond404($message)
    {
        $this->respondMessage(404, $message);
    }

    public function respond403($message)
    {
        $this->respondMessage(403, $message);
    }

    public function respond422($message)
    {
        $this->respondMessage(422, $message);
    }

    public function respond401($message)
    {
        $this->respondMessage(401, $message);
    }
}
