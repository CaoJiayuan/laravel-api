<?php
/**
 * Created by PhpStorm.
 * User: caojiayuan
 * Date: 2018/2/3
 * Time: 下午5:42
 */

namespace CaoJiayuan\LaravelApi\Areas;


use CaoJiayuan\LaravelApi\Areas\Entity\Area;
use CaoJiayuan\LaravelApi\Areas\Entity\City;
use CaoJiayuan\LaravelApi\Areas\Entity\Province;
use CaoJiayuan\LaravelApi\Http\Repository\Repository;

class AreaRepository extends Repository
{

  public function provinces()
  {
    return Province::all(['id', 'name']);
  }

  public function cities($provinceId = null)
  {
    $query = City::query();
    if ($provinceId !== null) {
      $query->where('province_id' , $provinceId);
    }

    return $query->get(['id', 'name']);
  }

  public function areas($cityId = null)
  {
    $query = Area::query();
    if ($cityId !== null) {
      $query->where('city_id' , $cityId);
    }

    return $query->get(['id', 'name']);
  }
}