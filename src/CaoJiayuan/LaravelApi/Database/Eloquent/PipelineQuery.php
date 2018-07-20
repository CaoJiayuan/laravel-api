<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/7/20
 * Time: 上午10:30
 */

namespace CaoJiayuan\LaravelApi\Database\Eloquent;


use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class PipelineQuery
{
    /**
     * @var Model
     */
    private $model;

    protected $alias = [
        '#^rand$#' => 'inRandomOrder',
        '#^sort:(.*)$#'  => 'orderBy:$1',
        '#^group:(.*)$#'  => 'groupBy:$1',
        '#^grep:(.*?),(.*)$#'  => 'where:$1,like,%$2%',
    ];

    protected $builder = null;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    protected function parsePipeString($pipeString)
    {
        $partials = explode('|', $pipeString);

        $pipes = [];
        foreach ($partials as $partial) {
            foreach ($this->alias as $key => $alias) {
                if (preg_match($key, $partial)) {
                    $partial = preg_replace($key, $alias, $partial);
                }
            }
            $formats = explode(':', $partial, 2);

            $pipes[] = [
                $formats[0],
                count($formats) > 1 ? explode(',', $formats[1]) : []
            ];
        }

        return $pipes;
    }

    public function getBuilder()
    {
        if ($this->builder == null) {
            $this->builder = $this->model->newQuery();
        }
        return $this->builder;
    }

    public function query($pipeString, callable $first = null)
    {
        $pipes = $this->parsePipeString($pipeString);

        return $this->throughPipes($pipes, $first);
    }



    public function __invoke($pipeString, callable $first = null)
    {
        return $this->query($pipeString, $first);
    }

    /**
     * @param callable $first
     * @param $pipes
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|mixed|static[]
     */
    public function throughPipes($pipes, callable $first = null)
    {
        $result = array_reduce($pipes, function ($builder, $pipe) {
            list($method, $arguments) = $pipe;
            if (is_null($builder)) {
                $builder = $this->getBuilder();
            }

            return call_user_func_array([$builder, $method], $arguments);
        }, $first ? $first($this->getBuilder()) : $this->getBuilder());

        if ($result instanceof Builder || $result instanceof EloquentBuilder) {
            return $result->get();
        }

        return $result;
    }
}
