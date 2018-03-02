<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/3/2
 * Time: 下午5:10
 */

namespace CaoJiayuan\LaravelApi\WebSocket;


use CaoJiayuan\LaravelApi\WebSocket\Events\WebSocketClosed;
use CaoJiayuan\LaravelApi\WebSocket\Events\WebSocketConnected;
use CaoJiayuan\LaravelApi\WebSocket\Events\WebSocketMessage;
use CaoJiayuan\LaravelApi\WebSocket\Events\WorkerStarted;
use Illuminate\Console\Command;
use Workerman\Worker;

class ServerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'laravel-api:ws {cmd=restart : Command to send} {--port=3000 : Listen port} {--count=4 : Work process} {--daemon=1 : Daemon mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A websocket server using workerman';

    public function handle()
    {
        $port = $this->option('port') ?: 3000;
        $count = $this->option('count') ?: 4;
        $cmd = $this->argument('cmd') ?: 'restart';
        $d = $this->option('daemon') ?: 0;
        global $argv;
        $argv[1] = $cmd;
        if ($d) {
            $argv[2] = '-d';
        } else {
            $argv[2] = '';
        }

        $worker = new Worker("websocket://0.0.0.0:{$port}");

        $worker->count = $count;
        $worker->onConnect = [$this, 'onConnect'];
        $worker->onMessage = [$this, 'onMessage'];
        $worker->onClose = [$this, 'onClose'];
        event(new WorkerStarted($worker));
        Worker::runAll();
    }

    public function onConnect($connection)
    {
        event(new WebSocketConnected($connection));
    }

    public function onMessage($connection, $data)
    {
        event(new WebSocketMessage($connection, $data));
    }

    public function onClose($connection)
    {
        event(new WebSocketClosed($connection));
    }
}
