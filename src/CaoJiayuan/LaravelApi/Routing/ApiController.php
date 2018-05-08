<?php
/**
 * Created by PhpStorm.
 * User: caojiayuan
 * Date: 17-9-26
 * Time: 下午4:55
 */

namespace CaoJiayuan\LaravelApi\Routing;

use CaoJiayuan\LaravelApi\Http\Request\RequestHelper;
use CaoJiayuan\LaravelApi\Http\Response\ResponseHelper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ResponseHelper, RequestHelper;
}
