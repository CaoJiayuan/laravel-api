<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/22
 * Time: 下午5:53
 */

namespace CaoJiayuan\LaravelApi\Html\Loader;


use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Cache\CacheManager;

class GuzzleLoader implements Loader
{
    protected $guzzle = null;
    protected $options = [];
    protected $loadOptions = [];
    protected $url;
    protected $method;
    protected $cacheExpire = 0;
    protected $cacheDriver = null;

    protected $cachePrefix = 'guzzle_document_loader:';

    public function __construct($url, $options = [], $method = 'GET')
    {
        $this->url = $url;
        $this->options = $options;
        $this->method = $method;
    }

    public function cache($expireMinutes = 0, $driver = null)
    {
        $this->cacheExpire = $expireMinutes;
        $this->setCacheDriver($driver);

        return $this;
    }

    public function config($options)
    {
        $this->options = $options;

        return $this;
    }

    public function header($key, $value = null)
    {
        if (!isset($this->options['headers'])) {
            $this->options['headers'] = [];
        }
        $headers = [];
        if (is_array($key)) {
            $headers = $key;
        } elseif (is_string($key) && !is_null($value)) {
            $headers = [
              $key => $value
            ];
        }
        foreach($headers as $k => $v) {
            $this->options['headers'][$k] = $v;
        }
    }

    public function userAgent($ua)
    {
        $this->header('User-Agent', $ua);

        return $this;
    }

    public function onLoad($options = [])
    {
        $this->loadOptions = $options;
        return $this;
    }

    public function proxyVia($via)
    {
        $this->loadOptions['proxy'] = $via;
        return $this;
    }

    /**
     * @return Client
     */
    public function getGuzzle()
    {
        if (is_null($this->guzzle)) {
            $this->guzzle = new Client($this->options);
        }

        return $this->guzzle;
    }

    public function load()
    {
        if ($this->cacheExpire > 0) {
            $key =  $this->getCacheKey($this->url);
            $driver = $this->getCacheDriver();

            $cb = function () {
                return $this->request()->__toString();
            };

            if ($this->cacheExpire == INF) {
                return $driver->rememberForever($key, $cb);
            }

            return $driver->remember($key, $this->cacheExpire, $cb);
        }

        return $this->request();
    }

    /**
     * @return CacheManager
     */
    protected function getCacheDriver()
    {
        return app('cache')->driver($this->cacheDriver);
    }

    public function loadAll(\Closure $fulfilled, \Closure $rejected = null, $concurrency = 5)
    {
        $results = [];
        $urls = array_wrap($this->url);

        $uncached = [];
        if ($this->cacheExpire > 0) {
            foreach($urls as $key => $url) {
                if ($cached = $this->getCacheDriver()->getStore()->get($this->getCacheKey($url))) {
                    array_push($results, $fulfilled($cached, $url));
                } else {
                    $uncached[] = $url;
                }
            }
        } else {
            $uncached = $urls;
        }


        $client = $this->getGuzzle();

        $requests = function () use ($uncached, $client) {
            foreach($uncached as $url) {
                yield function () use ($url, $client) {
                    return $client->requestAsync($this->method, $url, $this->loadOptions);
                };
            }
        };

        $pool = new Pool($client, $requests(), [
            'concurrency' => $concurrency,
            'fulfilled' => function ($response, $index) use ($fulfilled, &$results, $uncached) {
                /** @var \Psr\Http\Message\ResponseInterface $response */
                $url = $uncached[$index];
                $body = $response->getBody()->__toString();
                array_push($results, $fulfilled($body, $url));

                if ($this->cacheExpire > 0) {
                    $driver = $this->getCacheDriver();
                    $key = $this->getCacheKey($url);
                    if ($this->cacheExpire == INF) {
                        $driver->forever($key, $body);
                    } else {
                        $driver->put($key, $body, $this->cacheExpire);
                    }
                }
            },
            'rejected' => $rejected ?: function ($reason, $index) {

            },
        ]);

        $pool->promise()->wait();

        return $results;
    }

    /**
     * @return array
     */
    public function getLoadOptions()
    {
        return $this->loadOptions;
    }

    /**
     * @param string $cachePrefix
     * @return $this
     */
    public function setCachePrefix(string $cachePrefix)
    {
        $this->cachePrefix = $cachePrefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getCachePrefix(): string
    {
        return $this->cachePrefix;
    }

    /**
     * @param string $cacheDriver
     * @return $this
     */
    public function setCacheDriver($cacheDriver)
    {
        $this->cacheDriver = $cacheDriver;
        return $this;
    }

    /**
     * @return \Psr\Http\Message\StreamInterface
     */
    protected function request()
    {
        return $this->getGuzzle()->request($this->method, $this->url, $this->loadOptions)->getBody();
    }

    /**
     * @param $url
     * @return string
     */
    protected function getCacheKey($url): string
    {
        return $this->cachePrefix . md5($url);
    }

}
