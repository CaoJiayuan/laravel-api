<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/22
 * Time: 下午5:53
 */

namespace CaoJiayuan\LaravelApi\Html\Loader;


use GuzzleHttp\Client;

class GuzzleLoader implements Loader
{
    protected $guzzle = null;
    protected $options = [];
    protected $loadOptions = [];
    protected $url;

    public function __construct($url, $options = [])
    {
        $this->url = $url;
        $this->options = $options;
    }

    public function config($options)
    {
        $this->options = $options;

        return $this;
    }

    public function onLoad($options = [])
    {
        $this->loadOptions = $options;
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
        return $this->getGuzzle()->get($this->url, $this->loadOptions)->getBody()->__toString();
    }

    /**
     * @return array
     */
    public function getLoadOptions()
    {
        return $this->loadOptions;
    }
}
