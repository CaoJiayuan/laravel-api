<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/22
 * Time: 下午6:13
 */

namespace CaoJiayuan\LaravelApi\Html;


use CaoJiayuan\LaravelApi\Content\Html;
use CaoJiayuan\LaravelApi\Content\Text;
use DiDom\Element as BaseElement;
use DiDom\Query;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Class Element
 * @package CaoJiayuan\LaravelApi\Html
 * @method Element first($expression, $type = Query::TYPE_CSS, $wrapNode = true)
 * @method Element[]|NodeList find($expression, $type = Query::TYPE_CSS, $wrapNode = true)
 */
class Element extends BaseElement implements Arrayable
{

    public static function create($name, $value = null, array $attributes = [])
    {
        return new static($name, $value, $attributes);
    }

    /**
     * @return Html
     */
    public function html()
    {
        $string = parent::html();

        return new Html($string);
    }

    /**
     * @param string $delimiter
     * @return Text
     */
    public function innerHtml($delimiter = '')
    {
        $string = parent::innerHtml($delimiter);

        return new Html($string);
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

    public function setInnerHtml($html)
    {
        if (is_object($html) && method_exists($html, '__toString')) {
            $html = $html->__toString();
        }
        return parent::setInnerHtml($html);
    }

    public function attrGetChain($keys, $default = null)
    {
        $attrs = $this->attributes();

        foreach (array_wrap($keys) as $key) {
            if ($v = array_get($attrs, $key)) {
                return $v;
            }
        }

        return $default;
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

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        if ($this->isTextNode()) {
            return [
                'el'      => '$text',
                'attrs'   => [],
                'nodes'   => [],
                'content' => $this->text()
            ];
        }

        return [
            'el'      => $this->tag,
            'attrs'   => $this->attributes() ?: [],
            'nodes'   => array_map(function (Element $element) {
                return $element->toArray();
            }, $this->children()),
            'content' => $this->text()
        ];
    }

    public function children()
    {
        $children = [];

        foreach ($this->node->childNodes as $node) {
            if ($node instanceof \DOMCdataSection) {
                continue;
            }
            $children[] = new static($node);
        }

        return $children;
    }

    public function save($path)
    {
        file_put_contents($path, $this->html()->getOriginalContent());

        return $this;
    }
}
