<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/22
 * Time: 下午5:05
 */

namespace CaoJiayuan\LaravelApi\Html;


use DiDom\Document as BaseDocument;
use DiDom\Query;

class Document extends BaseDocument
{

    public function __construct($doc = null, $isFile = false, $encoding = 'UTF-8', $type = BaseDocument::TYPE_HTML)
    {
        if (is_string($doc) && starts_with($doc, 'http')) {
            $isFile = true;
        }

        BaseDocument::__construct($doc, $isFile, $encoding, $type);
    }

    public static function create($string = null, $isFile = false, $encoding = 'UTF-8', $type = BaseDocument::TYPE_HTML)
    {
        return new static($string, $isFile, $encoding, $type);
    }

    /**
     * @param string $expression
     * @param string $type
     * @param bool $wrapNode
     * @param null $contextNode
     * @return \Illuminate\Support\Collection
     */
    public function find($expression, $type = Query::TYPE_CSS, $wrapNode = true, $contextNode = null)
    {
        $result = parent::find($expression, $type, $wrapNode, $contextNode);

        return collect($result);
    }

    public function load($string, $isFile = false, $type = BaseDocument::TYPE_HTML, $options = null)
    {
        if ($string instanceof Loader\Loader) {
            $string = $string->load();
        }

        return parent::load($string, $isFile, $type, $options);
    }

    protected function wrapNode($node)
    {
        if (get_class($node) == 'DOMElement') {
            return new Element($node);
        }

        return parent::wrapNode($node);
    }

    /**
     * @param string $expression
     * @param string $type
     * @param bool $wrapNode
     * @param null $contextNode
     * @return Element|\DiDom\Element|\DOMElement
     */
    public function first($expression, $type = Query::TYPE_CSS, $wrapNode = true, $contextNode = null)
    {
        return parent::first($expression, $type, $wrapNode, $contextNode);
    }


}
