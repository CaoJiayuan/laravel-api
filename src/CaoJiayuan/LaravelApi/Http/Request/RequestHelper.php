<?php

namespace CaoJiayuan\LaravelApi\Http\Request;

use Illuminate\Http\Request;

trait RequestHelper
{

    public function getValidatedData(array $rules, array $messages = [], array $customAttributes = [])
    {
        /** @var Request $request */
        $request = app('request');
        list($keys, $fixedRules) = $this->resolveRules($rules, $request);

        $this->validate($request, $fixedRules, $messages, $customAttributes);

        $data = [];
        foreach ($keys as $key => $resolver) {
            $data[$key] = $resolver($request->get($key));
        }
        return $data;
    }

    protected function resolveRules(array $rules, $request)
    {
        $keys = [];
        $fixedRules = [];
        foreach ($rules as $key => $rule) {
            if (!is_numeric($key)) { // has role
                list($r, $resolver) = $this->getRulesFromValue($rule, $request);
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

    protected function getRulesFromValue($value, $request)
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
                    $resolver = function ($v) use ($value) {
                        return $v === null ? $value[1] : $v;
                    };
                }
            }
        } else {
            $rule = $value;
        }

        return [is_callable($rule) ? call_user_func($rule, $request) : $rule, $resolver];
    }
}
