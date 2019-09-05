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
    public function respondSuccess($message = 'Success', $data = [])
    {
        $this->respondMessageWithData(200, $message, $data);
    }

    public function respondMessageWithData($status, $message, $data = [])
    {
        if (!empty($data)) {
            $this->respondCustomMessage($status, $message, $status, $data);
        } else {
            $this->respondMessage($status, $message);
        }
    }

    public function respondCustomMessage($code, $message, $statusCode = 200, $data = [])
    {
        throw new CustomHttpException($code, $message, $data, $statusCode);
    }

    public function respondMessage($status, $message)
    {
        throw new HttpException($status, $message);
    }

    public function respond404($message, $data = [])
    {
        $this->respondMessageWithData(404, $message, $data);
    }

    public function respond403($message, $data = [])
    {
        $this->respondMessageWithData(403, $message, $data);
    }

    public function respond422($message, $data = [])
    {
        $this->respondMessageWithData(422, $message, $data);
    }

    public function respond401($message, $data = [])
    {
        $this->respondMessageWithData(401, $message, $data);
    }
}
