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
use DiDom\Query;
use JsonSerializable;

/**
 * Class Element
 * @package CaoJiayuan\LaravelApi\Html
 * @method Document first($expression, $type = Query::TYPE_CSS, $wrapNode = true)
 * @method Document find($expression, $type = Query::TYPE_CSS, $wrapNode = true)
 */
class Element extends BaseElement implements JsonSerializable
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

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->html();
    }
}
