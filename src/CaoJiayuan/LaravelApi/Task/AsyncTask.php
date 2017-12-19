<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2017/12/15
 * Time: 下午5:50
 */

namespace CaoJiayuan\LaravelApi\Task;

use CaoJiayuan\LaravelApi\Task\Contacts\Task;

abstract class AsyncTask implements Task
{
    abstract public function doingOnBackGround();

    protected $masterId = 0;

    protected $pid = 0;

    public function execute()
    {
        $pid = pcntl_fork();
        $this->masterId = posix_getpid();
        if (-1 === $pid) {
            throw new \RuntimeException('Can not fork');
        } else if ($pid) {
            $this->pid = $pid;
            pcntl_wait($status);
            echo $status;
            exit(0);
        } else {
            $result = $this->doingOnBackGround();
            $this->onExecuted($result);
            exit(0);
        }
    }

    public function onExecuted($result)
    {

    }
}
