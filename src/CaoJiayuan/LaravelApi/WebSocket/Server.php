<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/5/16
 * Time: 下午4:30
 */

namespace CaoJiayuan\LaravelApi\WebSocket;


use CaoJiayuan\LaravelApi\Contracts\Server as ServerInterface;

class Server implements ServerInterface
{

    public function open($port)
    {
        // TODO: Implement open() method.
        return $this;
    }

    public function host($host)
    {
        // TODO: Implement host() method.
        return $this;
    }

    public function getProtocol()
    {
        // TODO: Implement getProtocol() method.
    }
}
