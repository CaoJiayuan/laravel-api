<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2017/12/6
 * Time: 下午3:12
 */

namespace CaoJiayuan\LaravelApi\Http\Server;

use Illuminate\Contracts\Http\Kernel;
use CaoJiayuan\LaravelApi\FileSystem\MimeType\ExtensionMimeTypeGuesser;
use CaoJiayuan\LaravelApi\Http\UploadedFile;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Workerman\Connection\TcpConnection;
use Workerman\Lib\Timer;
use Workerman\Protocols\Http;
use Workerman\Worker;
use Illuminate\Support\Arr;

class ServerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'api-util:server {cmd=restart : Command to send} {--port=8888 : Listen port} {--count=4 : Work process} {--daemon=1 : Daemon mode} {--log=1 : Enable log}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A http server using workerman';

    /** @var Kernel */
    private $kernel;

    protected $tmpPath;

    protected $appName = 'Laravel server';

    protected $keepAlive = true;
    /**
     * @var Logger
     */
    protected $logger;


    public function __construct()
    {
        $this->initLogger();
        parent::__construct();
        $this->initCommand();
    }

    public function initLogger()
    {
        $handler = $this->getLoggerHandler();
        $this->logger = new Logger('Http server logger');
        $this->logger->setHandlers([$handler]);
    }

    protected function getLogPath()
    {
        return storage_path('logs/http-server.log');
    }

    public function initCommand()
    {
        $this->tmpPath = storage_path('app/tmp');
        if (!file_exists($this->tmpPath)) {
            mkdir($this->tmpPath, 0775);
        }
        $this->bindInstances();
        $this->kernel = app(Kernel::class);
        $this->appName = config('app.name');
    }

    public function bindInstances()
    {

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $port = $this->option('port') ?: 8888;
        $count = $this->option('count') ?: 4;
        $cmd = $this->argument('cmd') ?: 'restart';
        $d = $this->option('daemon') ?: 0;
        $l = $this->option('log');

        global $argv;
        $argv[1] = $cmd;
        if ($d) {
            $argv[2] = '-d';
        } else {
            $argv[2] = '';
        }

        $httpWorker = new Worker("http://0.0.0.0:{$port}");

        $this->beforeHandle($httpWorker);

        $httpWorker->name = $this->appName;
        $httpWorker->count = $count;

        /**
         * @param Worker $worker
         */
        $httpWorker->onWorkerStart = function ($worker) use ($port, $l) {
            $msg = "Http server opened on [0.0.0.0:$port], worker: [{$worker->id}]";
            $this->info($msg);
            $l && $this->logger->warn($msg);
        };

        /**
         * @param TcpConnection $connection
         * @param $data
         */
        $httpWorker->onMessage = function ($connection, $data) use ($d, $l) {
            $requestStart = microtime(true);

            $request = $this->generateRequest();

            $this->beforeRequest($request);
            $response = $this->handleRequest($request);
            $header = $response->headers->__toString();
            $headers = explode("\r\n", $header);
            foreach ((array)$headers as $h) {
                Http::header($h, true, $response->getStatusCode());
            }
            $connection->send($response->content());
            $this->afterRequest($request, $response);
            $this->keepAlive || $connection->close();
            $requestEnd = microtime(true);
            $requestTime = round($requestEnd - $requestStart, 4);
            $msg = $this->formatRequestLog($request, $response, $requestTime);
            $d || $this->info($msg);
            $l && $this->logger->info($msg);
        };

        Worker::runAll();
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param float $requestTime
     * @return string
     */
    protected function formatRequestLog($request, $response, $requestTime)
    {
        return "Handle request ({$request->getMethod()})[{$request->url()}] from [{$request->ip()}], status: ({$response->status()}), time : [{$requestTime}s]";
    }

    public function generateRequest()
    {
        $this->fixUploadFiles();
        $content = null;
        $post = $_POST;
        if (str_contains(Arr::get($_SERVER, 'HTTP_CONTENT_TYPE'), ['/json', '+json'])) {
            if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
                $content = json_encode($post);
            } else {
                $content = $GLOBALS['HTTP_RAW_REQUEST_DATA'];
                $post = json_decode($content, true) ?: [];
            }
        }

        return Request::create($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], array_merge($_GET, $post), $_COOKIE, $_FILES, $_SERVER, $content);
    }

    /**
     * @param Request $request
     */
    public function beforeRequest($request)
    {
    }

    public function beforeHandle(Worker $worker)
    {

    }

    /**
     * @param Request $request
     * @param Response $response
     */
    public function afterRequest($request, $response)
    {
        $this->kernel->terminate($request, $response);

        $this->clearAuth();

        \Session::flush();
    }

    public function clearAuth()
    {
        $auth = app('auth');

        $prop = new \ReflectionProperty($auth, 'guards');
        $prop->setAccessible(true);
        $prop->setValue($auth, []);

        if (app()->has('tymon.jwt')) {
            app('tymon.jwt')->unsetToken();
        }

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function handleRequest($request)
    {
        $staticPath = $this->getStaticPath();
        $uri = $request->getRequestUri();
        $uri = head(explode('?', $uri, 2));
        $file = $staticPath . $uri;
        if (is_file($file)) {
            return $this->handleFileRequest($request, $file);
        }
        $response = $this->handleHttpRequest($request);

        return $response;
    }

    public function getStaticPath()
    {
        return public_path();
    }

    public function handleHttpRequest($request)
    {
       return $this->kernel->handle($request);
    }

    public function fixUploadFiles()
    {
        global $_FILES;
        $files = $_FILES;
        $_FILES = [];

        foreach ((array)$files as $file) {
            $data = Arr::get($file, 'file_data');
            if ($data) {
                $tmpFile = $this->tmpPath . DIRECTORY_SEPARATOR . 'php' . str_random();
                $fp = fopen($tmpFile, 'w+');
                $size = Arr::get($file, 'file_size');
                $remain = $size;
                $i = 0;
                $chunk = 1024;
                do {
                    if ($remain < $chunk) {
                        $chunk = $remain;
                    }
                    $remain -= $chunk;
                    $readData = substr($data, $i, $chunk);
                    fwrite($fp, $readData, $chunk);
                    $i += $chunk;
                } while ($remain > 0);
                fclose($fp);
                $key = Arr::get($file, 'name');

                $fileName = Arr::get($file, 'file_name');
                $mimeType = Arr::get($file, 'file_type');
                $error = Arr::get($file, 'error', UPLOAD_ERR_OK);
                $uploadedFile = new UploadedFile($tmpFile, $fileName, $mimeType, $size, $error);
                $_FILES[$key] = $uploadedFile;
            }
        }

    }

    /**
     * @param Request $request
     * @param $file
     * @return mixed
     */
    public function handleFileRequest($request, $file)
    {

        $response = new Response();

        $fileInfo = new File($file);
        $mHeader = $request->header('If-Modified-Since');
        $mTime = $fileInfo->getMTime();

        $response->header('Last-Modified', Carbon::createFromTimestamp($mTime, 'UTC')->format('D, d M Y H:i:s') . ' GMT');
        if ($mTime - strtotime($mHeader) <= 0) {
            $response->setStatusCode(304);

            return $response;
        }
        $guesser = MimeTypeGuesser::getInstance();
        $guesser->register(new ExtensionMimeTypeGuesser());
        $contentType = $guesser->guess($fileInfo->getPathname());
        $response->header('Content-Type', $contentType);
        $fp = $fileInfo->openFile();
        $content = '';
        while (!$fp->eof()) {
            $content .= $fp->fread(1024);
        }
        $response->setContent($content);

        return $response;
    }

    /**
     * @return $this|\Monolog\Handler\HandlerInterface
     */
    protected function getLoggerHandler()
    {
        $handler = (new StreamHandler($this->getLogPath(), Logger::DEBUG))
            ->setFormatter(new LineFormatter(null, null, true, true));
        return $handler;
    }
}
