<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/6/25
 * Time: 下午2:49
 */

namespace CaoJiayuan\LaravelApi\Html;


use Illuminate\Support\Collection;

class NodeList extends Collection
{
    public function save($path)
    {
        file_put_contents($path, implode(PHP_EOL, $this->map(function (Element $element) {
               return $element->html()->getOriginalContent();
        })->toArray()));

        return $this;
    }
}
