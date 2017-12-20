<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2017/12/20
 * Time: 下午5:59
 */

namespace CaoJiayuan\LaravelApi\Http\Proxy;


use Illuminate\Console\Command;
use Workerman\Worker;
use Workerman\Connection\AsyncTcpConnection;

class HttpProxyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'laravel-api:proxy {cmd=start : Command to send} {--port=8080 : Listen port} {--count=4 : Work process} {--daemon=1 : Daemon mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A http proxy server using workerman';

    public function handle()
    {
        $port = $this->option('port') ?: 8080;
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

        $worker = new Worker("tcp://0.0.0.0:{$port}");
        $worker->count = $count;
        $worker->name = 'http-proxy';
        $worker->onMessage = function($connection, $buffer) use ($d)
        {
            list($method, $addr, $http_version) = explode(' ', $buffer);
            $url_data = parse_url($addr);
            $addr = !isset($url_data['port']) ? "{$url_data['host']}:80" : "{$url_data['host']}:{$url_data['port']}";
            // Async TCP connection.
            $remote_connection = new AsyncTcpConnection("tcp://$addr");
            // CONNECT.
            if ($method !== 'CONNECT') {
                $remote_connection->send($buffer);
                // POST GET PUT DELETE etc.
            } else {
                $connection->send("HTTP/1.1 200 Connection Established\r\n\r\n");
            }
            $d || $this->info("[$method] $addr");

            $remote_connection ->pipe($connection);
            $connection->pipe($remote_connection);
            $remote_connection->connect();
        };

        Worker::runAll();

    }
}
