<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/1/3
 * Time: 下午6:33
 */

namespace CaoJiayuan\LaravelApi\Session;


use Illuminate\Session\SessionManager as IlluminateSessionManager;

class SessionManager extends IlluminateSessionManager
{

    public function setDrivers($drivers = [])
    {
        $this->drivers = $drivers;
    }

    public function driver($driver = null)
    {
        $driver = $driver ?: $this->getDefaultDriver();
        $this->drivers[$driver] = $this->createDriver($driver);

        return $this->drivers[$driver];
    }
}
