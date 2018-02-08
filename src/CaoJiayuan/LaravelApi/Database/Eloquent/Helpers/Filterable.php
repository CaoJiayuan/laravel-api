<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/2/8
 * Time: 下午2:58
 */

namespace CaoJiayuan\LaravelApi\Database\Eloquent\Helpers;


use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    /**
     * @param Builder $builder
     * @param $filters
     * @param array $others
     * @param \Closure|null $then
     */
    public function applyFilters($builder, $filters, $others = [] , \Closure $then = null)
    {
        if (is_array($filters)) {
            $model = $builder->getModel();
            $searchables = $model->getFillable();

            $searchables = array_merge($others, $searchables);

            $table = $model->getTable();
            $builder->where(function ($query) use ($filters,$searchables,$table, $then) {
                foreach ($filters as $key => $value) {
                    list($key, $op) = $this->parseFilterKey($key);
                    if ($op == 'like') {
                        $value = \DB::raw("'%{$value}%'");
                    }
                    if (in_array($key, $searchables)){
                        /** @var Builder $query */
                        $column = $key;
                        if (strpos($key, '.') === false) {
                            $column = $table . '.' . $key;
                        }

                        $query->where($column, $op, $value);
                    } else {
                        $then && $then($query, $key, $value, $op);
                    }
                }
            });

        }
    }

    protected function parseFilterKey($key)
    {
        $partials = explode(',', $key);


        return [array_get($partials, 0), array_get($partials, 1, 'like')];
    }
}
