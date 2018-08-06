<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/8/6
 * Time: 上午10:48
 */

namespace CaoJiayuan\LaravelApi\Signature;


class Signature
{

    private $nonceLength;
    private $key;
    private $time;

    public function __construct($key, $nonceLength = 16)
    {
        $this->nonceLength = $nonceLength;
        $this->key = $key;
        $this->time = strval(time());
    }

    public function generate()
    {
        $nonce = str_random($this->nonceLength);
        $length = rand(1, 9);
        $timestamp = $this->time;
        return $nonce . strval($length) . strtoupper(md5($this->key . $timestamp . substr($nonce, 0, $length)));
    }

    public function __toString()
    {
        return $this->generate();
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }
}
