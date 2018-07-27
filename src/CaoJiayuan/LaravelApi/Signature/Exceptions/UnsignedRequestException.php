<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/7/27
 * Time: 上午11:43
 */

namespace CaoJiayuan\LaravelApi\Signature\Exceptions;


use Symfony\Component\HttpKernel\Exception\HttpException;

class UnsignedRequestException extends HttpException
{

    public function __construct($message = 'Access denied', $status = 403)
    {
        parent::__construct($status, $message);
    }
}
