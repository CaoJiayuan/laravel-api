<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/3/2
 * Time: 下午5:23
 */

namespace CaoJiayuan\LaravelApi\WebSocket\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Workerman\Worker;

class WorkerStarted
{
    use InteractsWithSockets, SerializesModels;

    /**
     * @var Worker
     */
    public $worker;

    public function __construct(Worker $worker)
    {
        $this->worker = $worker;
    }
}
