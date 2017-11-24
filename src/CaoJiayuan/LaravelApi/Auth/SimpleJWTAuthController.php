<?php
/**
 * Created by PhpStorm.
 * User: caojiayuan
 * Date: 17-9-8
 * Time: 下午2:08
 */

namespace CaoJiayuan\LaravelApi\Auth;


use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTGuard;

class SimpleJWTAuthController extends Controller
{

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * The user has been authenticated.
     *
     * @param  Request $request
     * @param  mixed $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        $data = [
            'access_token' => $this->getJWTToken($request, $user),
            'expires_in'   => $this->getTTl(),
            'type'         => 'bearer',
            'user'         => $user
        ];

        return response()->json($data);
    }

    protected function getJWTToken(Request $request, $user)
    {
        /** @var JWTGuard $guard */
        $guard = \Auth::guard('jwt');
        return $guard->login($user);
    }

    public function getTTl()
    {
        $guard = \Auth::guard('jwt');

        return $guard->factory()->getTTL() * 60;
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $jwtGuard = \Auth::guard('jwt');
        try {
            $jwtGuard->logout();
        } catch (\Exception $exception) {
        }

        return response()->json(['message' => 'Success']);
    }
}
