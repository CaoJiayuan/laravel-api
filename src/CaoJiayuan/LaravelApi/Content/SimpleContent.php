<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/22
 * Time: 下午6:19
 */

namespace CaoJiayuan\LaravelApi\Content;


class SimpleContent implements \JsonSerializable
{

    protected $content;

    /**
     * SimpleContent constructor.
     * @param string $content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    public function __toString()
    {
        return $this->content;
    }

    public function toString()
    {
        return $this->content;
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
        return $this->content;
    }
}
