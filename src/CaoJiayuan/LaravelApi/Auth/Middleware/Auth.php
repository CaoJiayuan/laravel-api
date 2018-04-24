<?php
/**
 * Not necessary login, parse user from JWT
 */

namespace CaoJiayuan\LaravelApi\Auth\Middleware;


use App\User;
use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWT;
use Tymon\JWTAuth\JWTGuard;
use Tymon\JWTAuth\Payload;

class Auth
{
    protected $guard = null;

    /**
     * @param null $name
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard|JWTGuard|JWT
     */
    protected function guard($name = null)
    {
        /** @var AuthManager $auth */
        $auth = app('auth');
        return $auth->guard($name);
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $this->guard = $guard;
        try {
            $token = $this->getToken($request);
            $payload =  $this->guard($guard)->getPayload($token);

            $user = $this->authenticateWithPayload($payload);
        } catch (\Exception $e) {

        }


        return $next($request);
    }

    /**
     * @param Request $request
     * @return bool|string
     */
    protected function getToken($request)
    {
        if (!$token = $this->guard($this->guard)->getToken()) {
            $token = $request->bearerToken();
        }

        return $token;
    }

    /**
     * @param Payload $payload
     * @return User
     */
    protected function authenticateWithPayload($payload)
    {
        $id = $payload->get('sub');

        $user = $this->getUser($id, $payload);

        return $user;
    }


    /**
     * @param $id
     * @param Payload $payload
     * @return User
     */
    public function getUser($id, $payload)
    {
        if (empty($id)) {
            return null;
        }

        $jwt = $this->guard($this->guard);
        $jwt->byId($id);
        return $jwt->user();
    }
}
