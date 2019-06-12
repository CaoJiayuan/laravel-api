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
use Illuminate\Support\Arr;

class Transfer implements ArrayAccess, Arrayable
{

    protected $data;

    protected $alias = [
        'time' => 'timestamp'
    ];

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

        if (empty($this->data)) {
            return $this->data;
        }

        if (is_array($template)) {
            return $this->transformWithTemplate($template);
        }


        return $this->data;
    }

    public function transformList($template)
    {

        if (!empty($this->data)) {
            if (!is_array(reset($this->data))) {
                throw new InvalidDataException('Invalid data giving');
            }
        }

        return array_map(function ($item) use ($template) {
            return (new static())->setData($item)->transform($template);
        }, Arr::wrap($this->data));
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

    public function formatTrim($v)
    {
        return trim($v);
    }

    public function formatDiff($v, $data, $diff = 0)
    {
        return $v + $diff;
    }

    public function take($key)
    {
        if (is_null($key)) {
            return $this;
        }

        $transfer = new static();

        return $transfer->setData(data_get($this->data, $key));
    }

    protected function transformWithTemplate($template)
    {
        $result = [];
        foreach ($template as $from => $to) {
            if (is_numeric($from)) {
                Arr::set($result, $to, data_get($this->data, $to));
            } else {
                $v = Arr::get($this->data, $from);
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
                    foreach ($k as $i) {
                        Arr::set($result, $i, $this->callFormats($formats, $v));
                    }
                } else {
                    Arr::set($result, $k, $this->callFormats($formats, $v));
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
        list($name, $params) = $this->parseFormatter($name);

        if (is_callable($name) && !is_string($name)) {
            return function ($v) use ($name, $params) {
                $arr = func_get_args();
                return call_user_func_array($name, array_merge($arr, $params));
            };
        }

        $method = $name;
        if (array_key_exists($name, $this->alias)) {
            $method = $this->alias[$name];
        }

        $formatMethod = 'format' . ucfirst($method);
        if (method_exists($this, $formatMethod)) {
            return function ($v) use ($formatMethod, $params) {
                $arr = func_get_args();

                return call_user_func_array([$this, $formatMethod], array_merge($arr, $params));
            };
        }

        return function ($v) use ($name) {
            return $v === null ? $name : $v;
        };
    }

    protected function parseFormatter($name)
    {
        if (!is_string($name)) {

            return [$name, []];
        }
        $formats = explode(':', $name, 2);
        if (count($formats) > 1) {
            $paramString = $formats[1];

            return [$formats[0], explode(',', $paramString)];
        }

        return [$formats[0], []];
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

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return static
     */
    public function setData($data)
    {
        $this->data = $this->morphData($data);
        return $this;
    }
}
