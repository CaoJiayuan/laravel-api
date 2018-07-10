<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/7/10
 * Time: 上午10:19
 */

namespace CaoJiayuan\LaravelApi\FileSystem;


use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class Downloader
{


    protected $clientOptions;

    protected $requestOptions;

    protected $url;

    public function __construct($url, $clientOptions = [], $requestOptions = [])
    {
        $this->clientOptions = $clientOptions;
        $this->requestOptions = $requestOptions;
        $this->url = $url;
    }


    public function download($path)
    {
        $client = new Client($this->clientOptions);

        list($dir, $name) = $this->parseDownloadPath($path);
        $this->makeDir($dir);

        $client->request('GET', $this->url, [
           RequestOptions::SINK => $dir . DIRECTORY_SEPARATOR . $name
        ]);

        return $path;
    }

    protected function makeDir($dir)
    {
        if (file_exists($dir)) {
            return true;
        }
        @mkdir($dir, 0775, true);
        return true;
    }

    protected function parseDownloadPath($path)
    {
        $rSlash = strrpos($path, '/');
        $dir = substr($path, 0, $rSlash);

        if (strlen($path) - 1 == $rSlash) {
            return [$dir, basename($this->url)];
        }

        return [$dir, substr($path, $rSlash + 1)];
    }

    /**
     * @param array $clientOptions
     * @return Downloader
     */
    public function setClientOptions(array $clientOptions)
    {
        $this->clientOptions = $clientOptions;
        return $this;
    }

    /**
     * @param array $requestOptions
     * @return Downloader
     */
    public function setRequestOptions(array $requestOptions)
    {
        $this->requestOptions = $requestOptions;
        return $this;
    }
}
