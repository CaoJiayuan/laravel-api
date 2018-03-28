<?php
/**
 * Created by PhpStorm.
 * User: caojiayuan
 * Date: 17-9-26
 * Time: 下午4:55
 */

namespace CaoJiayuan\LaravelApi\Routing;

use CaoJiayuan\LaravelApi\Http\Response\ResponseHelper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ResponseHelper;

    /**
     * @var Request
     */
    protected $request;

    public function __construct()
    {
        $this->request = app(Request::class);
    }

    public function inputGet($key, $default = null)
    {
        return $this->request->get($key, $default);
    }

    public function inputAll()
    {
        return $this->request->all();
    }

    public function validateRequest(array $rules, array $messages = [], array $customAttributes = [])
    {
        $this->validate($this->request, $rules, $messages, $customAttributes);
    }

    public function getValidatedData($rules, array $messages = [], array $customAttributes = [])
    {
        $fixedRules = [];

        $keys = [];

        foreach ($rules as $key => $rule) {
            if (!is_numeric($key)) {
                $fixedRules[$key] = $rule;
                str_contains($key, '.') || $keys[] = $key;
            } else {
                $keys[] = $rule;
            }
        }

        $this->validateRequest($fixedRules, $messages, $customAttributes);

        $data = $this->request->only($keys);
        return $data;
    }

}
