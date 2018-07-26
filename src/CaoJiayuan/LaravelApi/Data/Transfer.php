<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/7/25
 * Time: 下午1:56
 */

namespace CaoJiayuan\LaravelApi\Data;


use ArrayAccess;
use CaoJiayuan\LaravelApi\Data\Exceptions\InvalidDataException;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;

class Transfer implements ArrayAccess
{

    private $data;

    protected $alias = [
        'time' => 'timestamp'
    ];

    public function __construct($data)
    {
        $this->data = $this->morphData($data);
    }

    protected function morphData($data)
    {
        if ($data instanceof Arrayable) {
            return $data->toArray();
        }


        return $data;
    }

    public function transform($template)
    {
        if (is_callable($template)) {
            return $template($this->data);
        }
        if (is_array($template)) {
            return $this->transformWithTemplate($template);
        }


        return $this->data;
    }

    public function transformList($template)
    {
        if (!is_array(reset($this->data))) {
            throw new InvalidDataException('Invalid data giving');
        }

        return array_map(function ($item) use ($template) {
            return (new static($item))->transform($template);
        }, $this->data);
    }

    public function formatInt($v)
    {
        return intval($v);
    }

    public function formatTimestamp($v)
    {
        if (is_numeric($v)) {
            return $v;
        }

        return Carbon::parse($v)->timestamp;
    }

    public function take($key)
    {
        if (is_null($key)) {
            return $this;
        }
        $this->data = data_get($this->data, $key);

        return $this;
    }

    protected function transformWithTemplate($template)
    {
        $result = [];
        foreach ($template as $from => $to) {
            if (is_numeric($from)) {
                array_set($result, $to, data_get($this->data, $to));
            } else {
                $v = array_get($this->data, $from);
                if (is_array($to)) {
                    $k = array_shift($to);
                    $formats = $to;
                } else if (is_callable($to)) {
                    $k = $from;
                    $formats = [$to];
                } else {
                    $partials = explode('|', $to);
                    $k = array_shift($partials);
                    $formats = $partials;
                }
                if (is_array($k)) {
                    foreach($k as $i) {
                        array_set($result, $i, $this->callFormats($formats, $v));
                    }
                } else {
                    array_set($result, $k, $this->callFormats($formats, $v));
                }
            }
        }

        return $result;
    }

    protected function callFormats($formats, $v)
    {
        return array_reduce($formats, function ($carry, $format) {
            return $this->getFormat($format)($carry, $this->data);
        }, $v);
    }

    protected function getFormat($name)
    {
        if (is_callable($name) && !is_string($name)) {
            return function ($v) use ($name) {
                return call_user_func_array($name, func_get_args());
            };
        }

        $method = $name;
        if (array_key_exists($name, $this->alias)) {
            $method = $this->alias[$name];
        }

        $formatMethod = 'format' . ucfirst($method);
        if (method_exists($this, $formatMethod)) {
            return function ($v) use ($formatMethod) {
                return call_user_func_array([$this, $formatMethod], func_get_args());
            };
        }

        return function ($v) use ($name) {
            return $v === null ? $name : $v;
        };
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}
