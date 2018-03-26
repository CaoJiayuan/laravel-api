<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2017/12/20
 * Time: 下午5:59
 */

namespace CaoJiayuan\LaravelApi\Http\Proxy;


use Illuminate\Console\Command;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

class HttpProxyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'api-util:proxy {cmd=start : Command to send} {--port=8080 : Listen port} {--count=4 : Work process} {--daemon=1 : Daemon mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A http proxy server using workerman';

    protected $options = [

    ];

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

        /**
         * @param TcpConnection $connection
         * @param $buffer
         */
        $worker->onMessage = function ($connection, $buffer) use ($d) {
            list($method, $address, $HttpVersion) = explode(' ', $buffer);
            $UrlData = parse_url($address);
            $address = !isset($UrlData['port']) ? "{$UrlData['host']}:80" : "{$UrlData['host']}:{$UrlData['port']}";


            $remoteConnection = new AsyncTcpConnection("tcp://$address", $this->options);
            if ($method !== 'CONNECT') {
                $remoteConnection->send($buffer);
            } else {
                $connection->send("HTTP/1.1 200 Connection Established\r\n\r\n");
            }
            $d || $this->info("[$method] $address");

            $remoteConnection->pipe($connection);
            $connection->pipe($remoteConnection);
            $remoteConnection->connect();
        };

        Worker::runAll();

    }
}
