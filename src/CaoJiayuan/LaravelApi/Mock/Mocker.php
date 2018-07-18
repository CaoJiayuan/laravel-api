<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/7/18
 * Time: 上午11:16
 */

namespace CaoJiayuan\LaravelApi\Mock;


use CaoJiayuan\LaravelApi\Mock\Provider\Text;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class Mocker
 * @package CaoJiayuan\LaravelApi\Mock
 * @mixin Generator
 */
class Mocker
{
    protected $faker;

    protected $increase = null;

    protected $alias = [
        '#^(\d{1,})\+(\d{1,})$#' => 'increase:$2,$1',
        '#^(\d{1,})\-(\d{1,})$#' => 'increase:-$2,$1',
        '#^l:(.*)$#'             => 'list:$1',
    ];

    protected $l = Factory::DEFAULT_LOCALE;

    public function __construct($locale = Factory::DEFAULT_LOCALE)
    {
        $this->l = $locale;
        $this->faker = Factory::create($locale);
    }

    public static function zhCN()
    {
        $mocker = new static('zh_CN');

        $mocker->addProvider(new Text($mocker->faker));

        return $mocker;
    }

    /**
     * [
     *    'name|name',
     *     'data|list:15' => [
     *
     *      }
     * }
     *
     * @param $template
     * @return array|mixed
     */
    public function fromTemplate($template)
    {
        return $this->parseTemplate($template);
    }

    public function paginator($page = 1, $perPage = 15, $total, $itemTemplate)
    {
        $size = $perPage;

        if (($page * $perPage) > $total) {
            $size = $total - (($page - 1) * $perPage);
            if ($size < 0) {
                $size = 0;
            }
        }

        $items = $this->list($size, $itemTemplate);

        return new LengthAwarePaginator($items, $total, $perPage, $page);
    }

    protected function parseTemplate($t)
    {
        $result = [];
        if (is_array($t)) {
            foreach ($t as $key => $item) {
                if (is_numeric($key)) {
                    $k = $item;
                    $value = null;
                } else {
                    $k = $key;
                    $value = $item;
                }
                list($name, $rules) = $this->parseRule($k);
                if (is_null($rules)) {
                    $result[$name] = $this->useAsClosure($value)($this);
                } else {
                    $result[$name] = $this->formatPipeline($rules, $value);
                }
            }

        } else {
            list($name, $rules) = $this->parseRule('|' . $t);

            $result = $this->formatPipeline($rules, null);
        }

        return $result;
    }

    public function formatPipeline(array $pipes, $value)
    {

        return array_reduce($pipes, function ($carry, $item) {
            list($format, $arguments) = $item;

            return $this->format($format, $arguments, $carry);
        }, $value);
    }

    protected function useAsClosure($value)
    {
        if (is_callable($value)) {
            return $value;
        }

        return function () use ($value) {
            return $value;
        };
    }

    protected function parseRule($key)
    {
        $partials = explode('|', $key);
        /// 'name'
        if (count($partials) == 1) {
            return [$partials[0], null];
        }

        $first = array_shift($partials);
        $rules = [];
        foreach ($partials as $partial) {
            foreach ($this->alias as $key => $alias) {
                if (preg_match($key, $partial)) {
                    $partial = preg_replace($key, $alias, $partial);
                }
            }
            $formats = explode(':', $partial, 2);

            $rules[] = [
                $formats[0],
                count($formats) > 1 ? explode(',', $formats[1]) : []
            ];
        }

        return [$first, $rules];
    }

    public function format($format, $arguments = [], $value = null)
    {
        if (method_exists($this, $format)) {
            return call_user_func_array([$this, $format], array_merge($arguments, [$value]));
        }

        return $this->faker->format($format, array_merge($arguments, [$value]));
    }

    public function list($max, $value = [])
    {
        if (empty($value)) {
            $value = $max;
            $max = 20;
        }

        $result = [];

        $mocker = new static($this->l);

        for ($i = 0; $i < $max; $i++) {
            $result[] = $mocker->fromTemplate($value);
        }

        return $result;
    }

    public function increase($step = null, $value = null)
    {
        if (is_null($step)) {
            $step = 1;
        }

        if (is_null($value)) {
            $value = 1;
        }

        if ($this->increase === null) {
            $this->increase = intval($value);
        }

        $now = $this->increase;
        $this->increase += $step;
        return $now;
    }

    public function date($format = null, $now = null)
    {
        if (is_null($format)) {
            $format = 'Y-m-d H:i:s';
        }

        return date($format, $now ?: time());
    }

    public function time($str = null)
    {
        if (!is_null($str)) {
            return strtotime($str);
        }

        return time();
    }

    public function diff($min, $max = null, $value = null)
    {
        switch (func_num_args()) {
            case 1:
                return $min + 0;
            case 2:
                return $min + 0;
            case 3:
                return rand($min, $max) + $value;
        }

        return $min;
    }

    public function rand($min, $max, $value = null)
    {
        if (is_array($max)) {
            return $this->randomElements($max, $min);
        }

        if (!is_null($value)) {
            return $this->randomElements($value, rand($min, $max));
        }

        return rand($min, $max);
    }

    public function pick($num, $value = null)
    {
        if (is_null($value)) {
            $value = $num;
            $num = 1;
        }

        if ($num != 1 && is_array($value)) {
            return $this->randomElements($value, $num);
        }

        if (is_array($value)) {
            return $this->randomElement($value);
        }


        return $value;
    }


    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->faker, $name], $arguments);
    }

    public function __get($name)
    {
        return $this->faker->$name;
    }
}
