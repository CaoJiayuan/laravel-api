<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/10/26
 * Time: 下午4:46
 */

namespace CaoJiayuan\LaravelApi\Database\Eloquent\Helpers;


use CaoJiayuan\LaravelApi\Database\Eloquent\PipelineQuery;
use Illuminate\Database\Eloquent\Model;

trait UsingPipeline
{
    /**
     * @param Model $model
     * @param $pipeline
     * @param callable|null $first
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|mixed|Model[]
     */
    public function pipeline($model, $pipeline, callable $first = null)
    {
        $p = new PipelineQuery($model);

        return $p->query($pipeline, $first);
    }
}
