<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/5/9
 * Time: 上午10:29
 */

namespace CaoJiayuan\LaravelApi\Database\Query;


use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

class Raw extends Expression
{

    public function __construct($value)
    {
        if ($value instanceof Builder || $value instanceof EloquentBuilder || $value instanceof Relation) {
            $value = $this->toRawSql($value);
        }

        parent::__construct($value);
    }

    public function as($alias)
    {
        $this->value = '(' . $this->value . ') AS `' . $alias . '`';
        return $this;
    }

    /**
     * @param Builder $builder
     * @return string
     */
    public static function toRawSql($builder)
    {
        $sql = $builder->toSql();
        $bindings = $builder->getBindings();

        foreach ($bindings as $i => $binding) {
            if ($binding instanceof \DateTime) {
                $bindings[$i] = $binding->format('Y-m-d H:i:s');
            } elseif (is_string($binding)) {
                $bindings[$i] = str_replace("'", "\\'", $binding);
            }
        }

        $sql = str_replace(['%', '?', "\n"], ['%%', "'%s'", ' '], $sql);
        $raw = vsprintf($sql, $bindings);

        return $raw;
    }

    /**
     * @param $as
     * @return Builder
     */
    public function query($as)
    {
        return DB::table($this->as($as));
    }
}
