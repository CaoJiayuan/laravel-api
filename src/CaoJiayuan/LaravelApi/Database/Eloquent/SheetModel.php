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

class SheetModel extends Model
{
    use ExcelEntity;

}
