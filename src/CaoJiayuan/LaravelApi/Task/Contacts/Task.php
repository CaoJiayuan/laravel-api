<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2017/12/15
 * Time: 下午5:43
 */

namespace CaoJiayuan\LaravelApi\Task\Contacts;


interface Task
{
    public function execute();

    public function onExecuted($result);
}
