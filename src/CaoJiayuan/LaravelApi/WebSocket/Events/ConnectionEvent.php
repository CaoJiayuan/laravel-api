<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/3/2
 * Time: 下午5:34
 */

namespace CaoJiayuan\LaravelApi\WebSocket\Events;

use BadMethodCallException;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

/**
 * Class ConnectionEvent
 * @package CaoJiayuan\LaravelApi\WebSocket\Events
 * @mixin TcpConnection
 */
class ConnectionEvent
{
    use InteractsWithSockets, SerializesModels;

    /**
     * @var TcpConnection
     */
    public $connection;

    /**
     * @var Worker
     */
    public $worker;

    public function __construct(Worker $worker, TcpConnection $connection)
    {
        $this->connection = $connection;
        $this->worker = $worker;
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->connection, $name)) {
            return call_user_func_array([$this->connection, $name], $arguments);
        }

        throw new BadMethodCallException("Method {$name} does not exist.");
    }

    /**
     * @param $data
     */
    public function broadcastMessage($data)
    {
        /** @var TcpConnection[] $connections */
        $connections = $this->worker->connections;

        foreach ($connections as $connection) {
            $connection->send($data);
        }
    }
}
