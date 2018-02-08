<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/2/7
 * Time: 下午4:50
 */

namespace CaoJiayuan\LaravelApi\Database\Eloquent;


use CaoJiayuan\LaravelApi\Database\Eloquent\Helpers\ExcelEntity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class SheetModel
 * @package CaoJiayuan\LaravelApi\Database\Eloquent
 * @method static string exportSheet($name)
 */
class SheetModel extends Model
{
    use ExcelEntity;

    protected $excelHeaders = [];

    protected $excelColumnCasts = [];

    public function newEloquentBuilder($query)
    {
        $model = $this;

        Builder::macro('exportSheet', function ($name) use ($model) {
           $collection = $this->get();
           return $model->exportExcel($collection, $name);
        });
        return parent::newEloquentBuilder($query);
    }

    public function getExcelHeaders()
    {
        return $this->excelHeaders;
    }

    protected function getExcelColumnCasts()
    {
        return $this->excelColumnCasts;
    }
}
