<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/1/2
 * Time: 下午4:57
 */

namespace CaoJiayuan\LaravelApi\Http\Repository;

use CaoJiayuan\LaravelApi\Database\Eloquent\Helpers\Filterable;
use CaoJiayuan\LaravelApi\Database\Eloquent\Helpers\UsingPipeline;
use CaoJiayuan\LaravelApi\Database\Eloquent\PipelineQuery;
use CaoJiayuan\LaravelApi\Pagination\PageHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class Repository
{
    use PageHelper, Filterable;

    public function getSearchAbleData($model, array $search = [], \Closure $closure = null, \Closure $trans = null)
    {
        $data = Request::only([
            'filter', 'sort', 'per_page'
        ]);
        $data = array_merge([
            'filter'   => '',
            'sort'     => '',
            'per_page' => 15
        ], $data);
        list($filter, $order, $pageSize) = array_values($data);
        $builder = $this->getSearchableBuilder($model, $search, $closure, $order, $filter);

        $pager = $this->applyPaginate($builder, $pageSize);
        if ($trans) {
            $trans($pager->getCollection());
        }

        return $pager;
    }

    /**
     * @param Model $model
     * @param \Closure $closure
     * @param $order
     * @param Builder $builder
     * @return mixed
     */
    public function resolveSort($model, $order, $builder, \Closure $closure = null)
    {
        $orderArr = explode('|', $order, 2);
        $table = $model->getTable();
        $key = $model->getKeyName();
        $by = array_get($orderArr, 0);
        $direction = array_get($orderArr, 1);
        list($o, $d) = [$by ?: $table . '.' . $key, $direction ?: 'desc'];
        if ($closure) {
            $closure($builder);
        }
        if ($by) {
            $builder->getQuery()->orders = [];
            $builder->orderBy($o, $d);
        } else if (!$builder->getQuery()->orders) {
            $builder->orderBy($o, $d);
        }

        return $builder;
    }

    /**
     * @param $model
     * @param array $search
     * @param \Closure $closure
     * @param $order
     * @param $filter
     * @return Builder
     */
    public function getSearchableBuilder($model, array $search = [], \Closure $closure = null, $order = '', $filter = '')
    {
        $model = $this->getModelInstance($model);
        $builder = $model->newQuery();
        $table = $model->getTable();
        $this->resolveSort($model, $order, $builder, $closure);
        if ($filter && $search) {
            $builder->where(function ($builder) use ($search, $filter, $table) {
                foreach ((array)$search as $column) {
                    /** @var Builder $builder */

                    $key = $column;
                    if (strpos($column, '.') === false) {
                        $key = $table . '.' . $column;
                    }

                    $builder->orWhere($key, 'like binary', "%{$filter}%");
                }
            });
        }
        return $builder;
    }

    public function pipeline($model, $pipeline, callable $first = null)
    {
        $instance = $this->getModelInstance($model);
        $p = new PipelineQuery($instance);

        return $p->query($pipeline, $first);
    }

    /**
     * @param $model
     * @return Model
     * @throws \UnexpectedValueException
     */
    protected function getModelInstance($model)
    {
        if (!is_object($model)) {
            $model = app($model);
        }
        if (!$model instanceof Model) {
            throw new \UnexpectedValueException(__METHOD__ . ' expects parameter 1 to be an object of ' . Model::class . ',' . get_class($model) . ' given');
        }
        return $model;
    }
}
