<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/22
 * Time: 下午6:13
 */

namespace CaoJiayuan\LaravelApi\Html;


use CaoJiayuan\LaravelApi\Content\Text;
use DiDom\Element as BaseElement;

class Element extends BaseElement
{

    public static function create($name, $value = null, array $attributes = [])
    {
        return new static($name, $value, $attributes);
    }

    /**
     * @return Text
     */
    public function html()
    {
        $string = parent::html();

        return new Text($string);
    }

    /**
     * @param string $delimiter
     * @return Text
     */
    public function innerHtml($delimiter = '')
    {
        $string = parent::innerHtml($delimiter);

        return new Text($string);
    }

    /**
     * @param string $encoding
     * @return Document
     */
    public function toDocument($encoding = 'UTF-8')
    {
        $document = new Document(null, false, $encoding);

        $document->appendChild($this->node);

        return $document;
    }
}
