<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/4/10
 * Time: 上午10:25
 */

namespace CaoJiayuan\LaravelApi\Auth;


trait Authenticated
{

  public function getAuthId()
  {
    return \Auth::id();
  }

  public function getAuthUser()
  {
    return \Auth::user();
  }
}
