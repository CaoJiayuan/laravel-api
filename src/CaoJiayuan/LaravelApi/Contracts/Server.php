<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/5/16
 * Time: 下午4:32
 */

namespace CaoJiayuan\LaravelApi\Contracts;


interface Server
{
    public function open($port);

    public function host($host);

    public function getProtocol();
}
