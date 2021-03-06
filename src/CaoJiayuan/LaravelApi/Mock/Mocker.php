<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/7/18
 * Time: 上午11:16
 */

namespace CaoJiayuan\LaravelApi\Mock;


use CaoJiayuan\LaravelApi\Mock\Provider\Image;
use CaoJiayuan\LaravelApi\Mock\Provider\Text;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
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
        '#^ip$#'                 => 'ipv4',
        '#^string:(.*)$#'        => 'randomString:$1',
        '#^string$#'             => 'randomString:16',
        '#^null$#'                => 'mayNull',
        '#^\+(.*)$#'             => 'append:$1',
        '#^(.*)\+$#'             => 'prepend:$1',
    ];

    protected $l = Factory::DEFAULT_LOCALE;

    public function __construct($locale = Factory::DEFAULT_LOCALE)
    {
        $this->l = $locale;
        $this->faker = Factory::create($locale);
        if ($locale == 'zh_CN') {
            $this->faker->addProvider(new Text($this->faker));
            $this->faker->addProvider(new Image($this->faker));
        }
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

    public function paginator($total, $itemTemplate, $page = 1, $perPage = 15)
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
            if (count($formats) > 1) {
                $paramString = $formats[1];

                $formats[1] = $this->resolveParamString($paramString);
            }

            $rules[] = [
                $formats[0],
                count($formats) > 1 ? explode(',', $formats[1]) : []
            ];
        }

        return [$first, $rules];
    }

    protected function resolveParamString($string)
    {
        if (preg_match('#\{\{.*?\}\}#', $string)) {
            $result = preg_replace_callback('#\{\{(.*?)\}\}#', function ($match) {

                return $this->fromTemplate($match[1]);
            }, $string);

            return $result;
        }

        return $string;
    }

    public function format($format, $arguments = [], $value = null)
    {
        if ($value !== null) {
            $params = array_merge($arguments, [$value]);
        } else {
            $params = $arguments;
        }


        if (method_exists($this, $format)) {
            return call_user_func_array([$this, $format], $params);
        }

        return $this->faker->format($format, $params);
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
        } else if (is_null($now)) {
            $now = $format;
            $format = 'Y-m-d H:i:s';
        }
        if ($now instanceof \DateTime) {
            return $now->format($format);
        }

        return date($format, $now ?: time());
    }

    public function randDate($start, $end = 'now', $format = 'Y-m-d H:i:s')
    {
        $dt = $this->dateTimeBetween($start, $end);

        return $dt->format($format);
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
                if (is_string($max)) {
                    return $max . $min;
                }

                return $min + $max;
            case 3:
                return rand($min, $max) + $value;
        }

        return $min;
    }

    public function rand($min, $max, $value = null)
    {
        if (($min == 'true' && $max == 'false') || $max == 'true' && $min == 'false') {
            return $this->randomElement([true, false]);
        }

        if (is_array($max)) {
            return $this->randomElements($max, $min);
        }

        if (!is_null($value)) {
            if (is_array($value)) {
                return $this->randomElements($value, rand($min, $max));
            } else {
                return $this->randomString(rand($min, $max), $value);
            }
        }

        return rand($min, $max);
    }

    public function randomString($max, $seed = null)
    {
        if (is_null($seed)) {
            return str_random($max);
        }
        if ($max > mb_strlen($seed)) {
            return $seed;
        }
        $start = rand(0, mb_strlen($seed) - $max);

        return mb_substr($seed, $start, $max);
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
        } else if (is_string($value)) {
            return $this->randomString($num, $value);
        } else {
            return $this->pick($num, $this->from($value));
        }
    }

    public function append($append, $value)
    {
//        $append = $this->resolveMethodInjection($append);

        if (is_array($value)) {
            array_push($value, $append);
            return $value;
        }

        return $value . $append;
    }

    public function prepend($prepend, $value)
    {
//        $prepend = $this->resolveMethodInjection($prepend);
        if (is_array($value)) {
            array_unshift($value, $prepend);
            return $value;
        }

        return $prepend . $value;
    }

    public function item($value)
    {
        return $this->fromTemplate($value);
    }

    public function from($value)
    {
        $result = $this->useAsClosure($value)($this);
        if ($result instanceof Builder || $result instanceof EloquentBuilder) {
            $return = $result->get()->toArray();

            object_to_array($return, $result);
        }

        if ($result instanceof Arrayable) {
            $result = $result->toArray();
        }

        return $result;
    }

    public function db($param, $value = null, $other = null)
    {
        $partials = explode('.', $param, 2);
        $con = config('database.default');
        $table = $param;
        if (count($partials) > 1) {
            $con = $partials[0];
            $table = $partials[1];
        }

        $builder = \DB::connection($con)->table($table);
        if (is_null($value)) {
            $value = function (Builder $query) {
                return $query->get()->toArray();
            };
        } else {
            if (is_numeric($value)) {
                $value = function (Builder $query) use ($value, $other) {
                    $query->inRandomOrder()->take($value);
                    return $this->useAsClosure($other)($query);
                };
            } else {
                $value = function (Builder $query) use ($value, $other) {
                    if (is_null($other)) {
                        return $this->useAsClosure($value)($query);
                    }
                    return $this->useAsClosure($other)($query);
                };
            }
        }

        $return = $value($builder);
        if ($return instanceof Builder || is_null($return)) {
            $return = $builder->get()->toArray();
        }

        $result = [];
        object_to_array($return, $result);

        return $result;
    }

    public function join($glue, $value)
    {
        return implode($glue, $value);
    }

    public function split($delimiter, $value)
    {
        return explode($delimiter, $value);
    }

    public function mayNull($value)
    {
        return $this->randomElement([$value, null]);
    }

    protected function resolveMethodInjection($input)
    {
        if (preg_match('#^(.*?)([a-zA-Z]*?)\((.*?)\)(.*)$#', $input, $match)) {

            list($_, $p, $method, $paramString, $a) = $match;
            $resolved = $this->format($method, explode(',', $paramString));

            return $p . $resolved . $a;
        }

        return $input;
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
