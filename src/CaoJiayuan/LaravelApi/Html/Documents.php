<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/25
 * Time: 上午11:09
 */

namespace CaoJiayuan\LaravelApi\Html;


use CaoJiayuan\LaravelApi\Html\Loader\GuzzleLoader;
use Illuminate\Support\Collection;

class Documents extends Collection
{

    protected $fromString;
    protected $loader;
    protected $loaded = false;
    protected $concurrency = 5;

    public function __construct($items = [], $fromString = false)
    {
        $this->fromString = $fromString;
        $this->items = $this->getArrayableItems($items);
        $this->loader = new GuzzleLoader($this->items);
    }

    public function concurrency($concurrency)
    {
        $this->concurrency = $concurrency;
        return $this;
    }

    public function config($options)
    {
        $this->loader->config($options);
        return $this;
    }

    public function onLoad($options)
    {
        $this->loader->onLoad($options);
        return $this;
    }

    public function header($k, $v = null)
    {
        $this->loader->header($k, $v);
        return $this;
    }

    public function cache($minutes, $driver = null)
    {
        $this->loader->setCacheDriver($driver)->cache($minutes);
        return $this;
    }

    public function load(\Closure $onLoad = null)
    {
        if ($this->fromString) {
            $this->items = array_map(function ($item) use ($onLoad) {
                $doc = new Document($item);
                $onLoad && $onLoad($doc, $item);
                return $doc;
            }, $this->items);
        } else {
            $this->items = $this->loader->loadAll(function ($body, $url) use ($onLoad) {
                $doc = new Document($body);
                $onLoad && $onLoad($doc, $url);
                return $doc;
            }, null, $this->concurrency);
        }

        $this->loaded = true;

        return $this;
    }

    public static function loadFrom(array $urls)
    {
        return new static($urls);
    }

    public static function loadStrings(array $strings)
    {
        return new static($strings, true);
    }
}
