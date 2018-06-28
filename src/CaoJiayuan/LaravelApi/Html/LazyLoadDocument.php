<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/28
 * Time: 下午3:25
 */

namespace CaoJiayuan\LaravelApi\Html;


use CaoJiayuan\LaravelApi\Html\Loader\ContentLoader;
use CaoJiayuan\LaravelApi\Html\Loader\GuzzleLoader;
use CaoJiayuan\LaravelApi\Ob\ObjectOb;
use CaoJiayuan\LaravelApi\Ob\Value;

/**
 * Class LazyLoadDocument
 * @package CaoJiayuan\LaravelApi\Html
 * @mixin Document
 */
class LazyLoadDocument extends ObjectOb
{
    protected $loader = null;
    protected $document = null;


    /**
     * Doc constructor.
     * @param mixed $load
     */
    public function __construct($load)
    {
        if (starts_with($load, ['http://', 'https://']))  {
            $this->loader = new GuzzleLoader($load);
        } else {
            $this->loader = new ContentLoader($load);
        }
        $this->document = new Document();
        $value = new Value($this->document);

        parent::__construct($value);
    }

    /**
     * @return Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    protected function _watch_($now, $old)
    {

    }

    /**
     * @param Document $value
     * @param $param
     * @return mixed
     */
    protected function _reading_($value, $param)
    {
        if ($this->_using_()) {
            return $value;
        }

        $value->load($this->loader);
    }

    /**
     * @return ContentLoader|GuzzleLoader
     */
    public function getLoader()
    {
        return $this->loader;
    }

    public function callLoaderMethod($method, $arguments)
    {
        return call_user_func_array([$this->getLoader(), $method], $arguments);
    }
}
