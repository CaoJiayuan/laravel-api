<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/28
 * Time: 下午3:53
 */

namespace CaoJiayuan\LaravelApi\Html;


use CaoJiayuan\LaravelApi\Ob\ObjectOb;
use CaoJiayuan\LaravelApi\Ob\Value;

/**
 * Class LazyLoadDocuments
 * @package CaoJiayuan\LaravelApi\Html
 * @mixin Documents
 */
class LazyLoadDocuments extends ObjectOb
{
    protected $dontTrigger = ['config', 'onLoad', 'header', 'cache', 'load'];

    protected $loaded = false;
    protected $documents = null;

    public function __construct($items, $string = false)
    {
        $this->documents = new Documents($items, $string);
        $value = new Value($this->documents);

        parent::__construct($value);
    }

    /**
     * @return Documents
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    protected function _watch_($now, $old)
    {

    }

    /**
     * @param Documents $value
     * @param $param
     * @return mixed
     */
    protected function _reading_($value, $param)
    {
        if ($this->loaded) {
            return $value;
        }
        if (in_array($param[0], $this->dontTrigger)) {
            $this->loaded = false;
            return $value;
        }
        $value->load();
        $this->loaded = true;
    }
}
