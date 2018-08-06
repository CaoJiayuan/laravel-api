<?php
/**
 * Created by PhpStorm.
 * User: caojiayuan
 * Date: 2018/2/3
 * Time: 下午5:46
 */

namespace CaoJiayuan\LaravelApi\Areas\Controllers;


use App\Http\Controllers\Controller;
use CaoJiayuan\LaravelApi\Areas\AreaRepository;
use Illuminate\Http\Request;

/**
 * @apiDefine ResponseArea
 * @apiSuccess {Number} id ID
 * @apiSuccess {String} name 名称
 */

/**
 * @api {GET} areas/areas 获取区域
 * @apiGroup Common
 * @apiParam {Number} city_id 城市ID
 * @apiUse ResponseArea
 */

/**
 * @api {GET} areas/cities 获取城市
 * @apiGroup Common
 * @apiParam {Number} province_id 省份id
 * @apiUse ResponseArea
 */

/**
 * @api {GET} areas/provinces 获取省份
 * @apiGroup Common
 * @apiUse ResponseArea
 */
/**
 * Class AreaController
 * @package App\Http\Controllers\Api
 */
class AreaController extends Controller
{

  /**
   * @param AreaRepository $repository
   * @return \Illuminate\Database\Eloquent\Collection|static[]
   */
  public function provinces(AreaRepository $repository)
  {
    return $repository->provinces();
  }

  /**
   * @param AreaRepository $repository
   * @param Request $request
   * @return \Illuminate\Database\Eloquent\Collection|static[]
   */
  public function cities(AreaRepository $repository, Request $request)
  {
    $provinceId = $request->get('province_id');

    return $repository->cities($provinceId);
  }


  /**
   * @param AreaRepository $repository
   * @param Request $request
   * @return \Illuminate\Database\Eloquent\Collection|static[]
   */
  public function areas(AreaRepository $repository, Request $request)
  {
    $cityId = $request->get('city_id');

    return $repository->areas($cityId);
  }
}
