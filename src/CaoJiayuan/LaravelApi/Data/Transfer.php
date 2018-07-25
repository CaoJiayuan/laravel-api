<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/7/25
 * Time: 下午1:56
 */

namespace CaoJiayuan\LaravelApi\Data;


use CaoJiayuan\LaravelApi\Data\Exceptions\InvalidDataException;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;

class Transfer
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

    protected function transformWithTemplate($template)
    {
        $result = [];
        foreach ($template as $from => $to) {
            if (is_numeric($from)) {
                $result[$to] = data_get($this->data, $to);
            } else {
                $v = array_get($this->data, $from);
                if (is_array($to)) {
                    $k = array_shift($to);
                    $formats = $to;
                } else {
                    $partials = explode('|', $to);
                    if (count($partials) == 1) {
                        $k = $from;
                        $formats = [$partials[0]];
                    } else {
                        $k = array_shift($partials);
                        $formats = $partials;
                    }
                }
                $result[$k] = $this->callFormats($formats, $v);
            }
        }

        return $result;
    }

    protected function callFormats($formats, $v)
    {
        return array_reduce($formats, function ($carry, $format) {
            return $this->getFormat($format)($carry);
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
}
