<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2017/12/6
 * Time: 下午3:12
 */

namespace CaoJiayuan\LaravelApi\Http\Server;

use App\Http\Kernel;
use CaoJiayuan\LaravelApi\Http\UploadedFile;
use Carbon\Carbon;
use CaoJiayuan\LaravelApi\FileSystem\MimeType\ExtensionMimeTypeGuesser;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http;
use Workerman\Worker;


class ServerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'laravel-api:server {cmd=restart : Command to send} {--port=8888 : Listen port} {--count=4 : Work process} {--daemon=1 : Daemon mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A http server using workerman';

    /** @var Kernel */
    private $kernel;

    protected $tmpPath;


    public function __construct()
    {
        $this->tmpPath = storage_path('app/tmp');
        if (!file_exists($this->tmpPath)){
            mkdir($this->tmpPath, 0775);
        }
        $this->kernel = app(Kernel::class);
        parent::__construct();
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

        global $argv;
        $argv[1] = $cmd;
        if ($d) {
            $argv[2] = '-d';
        } else {
            $argv[2] = '';
        }

        $httpWorker = new Worker("http://0.0.0.0:{$port}");

        $httpWorker->name = config('app.name');
        $httpWorker->count = $count;

        /**
         * @param Worker $worker
         */
        $httpWorker->onWorkerStart = function ($worker) use ($port) {
            $this->info("Http server open on [0.0.0.0:$port], worker: [{$worker->id}]");
        };

        /**
         * @param TcpConnection $connection
         * @param $data
         */
        $httpWorker->onMessage = function ($connection, $data) use($d) {
            $requestStart = microtime(true);
            $this->fixUploadFiles();
            $content = null;
            $post = $_POST;
            if (str_contains(array_get($_SERVER, 'HTTP_CONTENT_TYPE'), ['/json', '+json'])) {
                if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
                    $content = json_encode($post);
                } else {
                    $content = $GLOBALS['HTTP_RAW_REQUEST_DATA'];
                    $post = json_decode($content, true) ?: [];
                }
            }

            $request = Request::create($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], array_merge($_GET, $post), $_COOKIE, $_FILES, $_SERVER, $content);

            $response = $this->handleRequest($request);
            $header = $response->headers->__toString();
            $headers = explode("\r\n", $header);
            foreach ((array)$headers as $h) {
                Http::header($h, true, $response->getStatusCode());
            }
            $connection->send($response->content());
            $this->kernel->terminate($request, $response);
            $requestEnd = microtime(true);
            $requestTime = round($requestEnd - $requestStart, 4);
            $d || $this->info("Handle request ({$request->getMethod()})[{$request->url()}] from [{$request->ip()}], status: ({$response->status()}), time : [{$requestTime}s]");
        };

        Worker::runAll();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function handleRequest($request)
    {

        $staticPath = public_path();
        $uri = $request->getRequestUri();
        $uri = head(explode('?',$uri,  2));
        $file = $staticPath . $uri;
        if (is_file($file)) {
            return $this->handleFileRequest($request, $file);
        }
        $response = $this->kernel->handle($request);

        return $response;
    }

    public function fixUploadFiles()
    {
        global $_FILES;
        $files = $_FILES;
        $_FILES = [];

        foreach ((array)$files as $file) {
            $data = array_get($file, 'file_data');
            if ($data) {
                $tmpFile = $this->tmpPath . DIRECTORY_SEPARATOR . 'php' . str_random();
                $fp = fopen($tmpFile, 'w+');
                $size = array_get($file, 'file_size');
                $remain = $size;
                $i = 0;
                $chunk = 1024;
                do {
                    if ($remain < $chunk){
                        $chunk = $remain;
                    }
                    $remain -= $chunk;
                    $readData = substr($data, $i, $chunk);
                    fwrite($fp, $readData, $chunk);
                    $i += $chunk;
                } while ($remain > 0);
                fclose($fp);
                $key = array_get($file, 'name');

                $fileName = array_get($file, 'file_name');
                $mimeType = array_get($file, 'file_type');
                $error = array_get($file, 'error', UPLOAD_ERR_OK);
                $uploadedFile = new UploadedFile($tmpFile, $fileName, $mimeType, $size, $error);
                $_FILES[$key]  = $uploadedFile;
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
}