<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/4/8
 * Time: 下午6:17
 */

namespace CaoJiayuan\LaravelApi\WebSocket\Broadcaster;


interface Subscriber
{
    public function notification($payload);

    public function getId();
}
