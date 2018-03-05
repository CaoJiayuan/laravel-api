<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/3/2
 * Time: 下午5:20
 */

namespace CaoJiayuan\LaravelApi\WebSocket\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

class WebSocketMessage extends ConnectionEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Worker $worker, TcpConnection $connection, $message)
    {
        $this->message = $message;
        parent::__construct($worker, $connection);
    }

    public function convertMessage($assoc = true)
    {
        if ($m = json_decode($this->message, $assoc)) {
            $this->message = $m;
        }
        return $this;
    }
}
