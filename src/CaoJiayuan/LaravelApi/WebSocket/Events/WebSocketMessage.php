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

class WebSocketMessage extends ConnectionEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(TcpConnection $connection, $message)
    {
        $this->message = $message;
        parent::__construct($connection);
    }
}
