<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/1/24
 * Time: 下午4:22
 */

namespace CaoJiayuan\LaravelApi\Http\Server;
use Illuminate\Support\Facades\Session;
use Laravel\Lumen\Application;
use Workerman\Connection\TcpConnection;
use Workerman\Lib\Timer;
use Workerman\Protocols\Http;
use Workerman\Worker;

class LumenServerCommand extends ServerCommand
{


    protected $signature = 'api-util:server {cmd=restart : Command to send} {--port=8888 : Listen port} {--count=4 : Work process} {--daemon=1 : Daemon mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A http server using workerman';

    protected $appName = 'Lumen server';

    /**
     * @var Application
     */
    protected $app;

    public function __construct()
    {
        parent::__construct();
    }

    public function initCommand()
    {
        $this->tmpPath = storage_path('app/tmp');
        if (!file_exists($this->tmpPath)) {
            mkdir($this->tmpPath, 0775);
        }
        $this->app = require base_path('bootstrap/app.php');
    }

    public function getStaticPath()
    {
        return base_path('public');
    }
    public function handleHttpRequest($request)
    {
        return $this->app->handle($request);
    }

    public function afterRequest($request, $response)
    {
        $this->clearAuth();
    }
}