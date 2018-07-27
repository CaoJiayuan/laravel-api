<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/7/27
 * Time: 上午11:28
 */

namespace CaoJiayuan\LaravelApi\Signature;


class Validator
{

    private $key;
    private $nonceLength;

    public function __construct($key, $nonceLength)
    {
        $this->key = $key;
        $this->nonceLength = $nonceLength;
    }

    public function validate($signature, $timestamp)
    {
        $nonce = substr($signature, 0, $this->nonceLength);
        $length = substr($signature, $this->nonceLength, 1);

        $sign = $nonce . $length . strtoupper(md5($this->key . $timestamp . substr($nonce, 0, $length)));

        return $sign === $signature;
    }
}
