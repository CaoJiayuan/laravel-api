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


class SheetModel extends Model
{
    use ExcelEntity;

    protected $excelHeaders = [];


    public function getExcelHeaders()
    {
        return $this->excelHeaders;
    }

}
