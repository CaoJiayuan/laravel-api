<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/7/20
 * Time: 上午10:45
 */

namespace CaoJiayuan\LaravelApi\Database\Eloquent\Helpers;


use CaoJiayuan\LaravelApi\Database\Eloquent\PipelineQuery;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait Pipelineable
 * @package CaoJiayuan\LaravelApi\Database\Eloquent\Helpers
 * @method static mixed pipeline($pipeline, callable $first = null)
 */
trait Pipelineable
{
    public static function bootPipelineable()
    {
        Builder::macro('pipeline', function ($pipeline, callable $first = null) {
            /** @var Builder $this */
            $model = $this->getModel();
            $p = new PipelineQuery($model);

            return $p($pipeline, function () {
                return $this;
            });
        });

    }
}
