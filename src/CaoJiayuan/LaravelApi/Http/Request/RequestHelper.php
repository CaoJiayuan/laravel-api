<?php

namespace CaoJiayuan\LaravelApi\Http\Request;

use Illuminate\Http\Request;

trait RequestHelper
{

  public function getValidatedData(array $rules, array $messages = [], array $customAttributes = [])
  {
    list($keys, $fixedRules) = $this->resolveRules($rules);

    /** @var Request $request */
    $request = app('request');
    $this->validate($request, $fixedRules, $messages, $customAttributes);

    $data = [];
    foreach ($keys  as $key => $resolver) {
      $data[$key] = $resolver($request->get($key));
    }
    return $data;
  }

  protected function resolveRules(array $rules)
  {
    $keys = [];
    $fixedRules = [];
    foreach ($rules as $key => $rule) {
      if (!is_numeric($key)) { // has role
        list($r, $resolver) = $this->getRulesFromValue($rule);
        $r && $fixedRules[$key] = $r;
        str_contains($key, '.') || $keys[$key] = $resolver;
      } else {
        $keys[$rule] = function ($v) {
          return $v;
        };
      }
    }

    return [$keys, $fixedRules];
  }

  protected function getRulesFromValue($value)
  {
    $resolver = function ($v) {
      return $v;
    };

    if (is_array($value)) {

      if (isset($value[0])) { // ex: ['required']
        $rule = $value[0];
      } else {
        $rule = null;
      }

      if (isset($value[1])) {
        if (is_callable($value[1])) { // ex: ['required', function($v) { return $v + 1;}]
          $resolver = $value[1];
        } else { // ex: ['required', 1]
          $resolver = function () use ($value) {
            return $value[1];
          };
        }
      }
    } else {
      $rule = $value;
    }

    return [$rule, $resolver];
  }
}
