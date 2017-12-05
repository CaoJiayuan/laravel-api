<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2017/12/5
 * Time: 下午2:38
 */

namespace CaoJiayuan\LaravelApi\Aliyun\Sts;


use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class StsAuthException extends HttpException
{


    /**
     * @var array
     */
    public $responseData;
    /**
     * @var int
     */
    public $statusCode;

    /**
     * StsAuthException constructor.
     * @param array $responseData
     * @param int $statusCode
     * @param string $message
     */
    public function __construct($responseData, $statusCode, $message = '')
    {
        $this->responseData = $responseData;
        $this->statusCode = $statusCode;
        parent::__construct($statusCode, $message);
    }
}
