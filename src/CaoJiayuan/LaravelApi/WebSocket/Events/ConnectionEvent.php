<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/3/2
 * Time: 下午5:34
 */

namespace CaoJiayuan\LaravelApi\WebSocket\Events;

use BadMethodCallException;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Workerman\Connection\TcpConnection;

/**
 * Class ConnectionEvent
 * @package CaoJiayuan\LaravelApi\WebSocket\Events
 * @mixin TcpConnection
 */
class ConnectionEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var TcpConnection
     */
    public $connection;

    public function __construct(TcpConnection $connection)
    {
        $this->connection = $connection;
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->connection, $name)) {
            return call_user_func_array([$this->connection, $name], $arguments);
        }

        throw new BadMethodCallException("Method {$name} does not exist.");
    }
}
