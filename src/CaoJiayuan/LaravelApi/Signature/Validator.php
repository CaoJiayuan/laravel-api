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
        $sign = $this->regenerate($signature, $timestamp);

        return $sign === $signature;
    }

    /**
     * @param $signature
     * @param $timestamp
     * @return string
     */
    public function regenerate($signature, $timestamp)
    {
        $nonce = $this->getNonce($signature);
        $length = $this->getLength($signature);

        $sign = $nonce . $length . strtoupper(md5($this->key . $timestamp . substr($nonce, 0, $length)));
        return $sign;
    }

    /**
     * @param $signature
     * @return bool|string
     */
    public function getNonce($signature)
    {
        return substr($signature, 0, $this->nonceLength);
    }

    /**
     * @param $signature
     * @return bool|string
     */
    public function getLength($signature)
    {
        return substr($signature, $this->nonceLength, 1);
    }
}
