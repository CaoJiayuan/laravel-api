<?php
/**
 * Created by Cao Jiayuan.
 * Date: 17-4-10
 * Time: 下午4:39
 */

namespace CaoJiayuan\LaravelApi\Auth\OAuth;


use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OAuth
{
    protected static $authUrls = [
        'qq'     => 'https://graph.qq.com/oauth2.0/authorize',
        'weibo'  => 'https://api.weibo.com/oauth2/authorize',
        'wechat' => 'https://open.weixin.qq.com/connect/qrconnect',
    ];
    protected static $tokenUrls = [
        'qq'     => 'https://graph.qq.com/oauth2.0/token',
        'weibo'  => 'https://api.weibo.com/oauth2/access_token',
        'wechat' => 'https://api.weixin.qq.com/sns/oauth2/access_token',
    ];
    protected static $tokenInfo = [
        'qq'     => 'https://graph.qq.com/oauth2.0/me',
        'weibo'  => 'https://api.weibo.com/oauth2/get_token_info',
        'wechat' => '',
    ];
    protected static $userInfo = [
        'qq'     => 'https://graph.qq.com/user/get_user_info',
        'weibo'  => 'https://api.weibo.com/2/users/show.json',
        'wechat' => 'https://api.weixin.qq.com/sns/userinfo',
    ];

    public static function goPage($type)
    {
        $url = self::getRedirectUrl($type);

        return redirect($url);
    }

    public static function getAuth($type)
    {
        list($token, $id) = self::getToken($type);

        if ($id) {
            $openid = $id;
        } else {
            $openid = self::getOpenId($type, $token);
            if (!$openid) {
                \Log::alert('Oauth failed:' . $type);
                throw new HttpException(401, '获取登陆信息失败');
            }
        }
        $info = self::getUserInfo($type, $openid, $token);

        return array_merge([
            'openId' => $openid,
        ], $info);
    }

    public static function getToken($type)
    {
        $code = \Request::get('code');
        $redirect = url(env('API_PREFIX', '') . "/OAuth/{$type}");
        list($clientId, $secret) = self::getAppInfo($type);
        $data = [
            'client_id'     => $clientId,
            'appid'         => $clientId,
            'secret'        => $secret,
            'client_secret' => $secret,
            'redirect_uri'  => $redirect,
            'grant_type'    => 'authorization_code',
            'code'          => $code,
        ];
        $url = static::$tokenUrls[$type];
        if ($type == 'weibo') {
            $body = static::post($url, 'query', $data);
        } else {
            $body = static::get($url, 'query', $data);
        }
        if ($type == 'qq' && is_string($body)) {
            $body = \GuzzleHttp\Psr7\parse_query($body);
        }
        $token = Arr::get($body, 'access_token');
        $openid = Arr::get($body, 'openid');

        if (!$token) {
            throw new HttpException(401, '获取登陆信息失败');
        }

        return [$token, $openid];
    }

    public static function post($uri, $dataType = RequestOptions::JSON, $data = [])
    {
        return static::exec($uri, 'POST', [
            $dataType => $data
        ]);
    }

    /**
     * @param $url
     * @param $method
     * @param array $options
     * @return array|\Psr\Http\Message\StreamInterface|string
     */
    public static function exec($url, $method, $options = [])
    {
        $response = static::getClient()->request($method, $url, $options);
        return static::parseResponse($response);
    }

    public static function getClient()
    {
        $opts = [
            'http_errors' => false,
            'verify'      => false,
        ];
        return new Client($opts);
    }

    /**
     * @param $response \Psr\Http\Message\ResponseInterface
     * @return \Psr\Http\Message\StreamInterface|array|string
     */
    public static function parseResponse($response)
    {
        $body = $response->getBody()->__toString();
        if (preg_match('#callback\((.*?)\)#', $body, $match)) {
            $body = trim($match[1]);
        }
        if ($decode = json_decode($body, true)) {
            return $decode;
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode != 200) {
            \Log::info('Oauth rejected', [
                'code' => $statusCode,
                'msg'  => $body,
            ]);
        }
        return $body;
    }

    public static function get($uri, $dataType = RequestOptions::QUERY, $data = [])
    {
        return static::exec($uri, 'GET', [
            $dataType => $data
        ]);
    }

    /**
     * @param $type
     * @param $token
     * @return mixed
     */
    public static function getOpenId($type, $token)
    {
        $data = [
            'access_token' => $token,
        ];
        $url = static::$tokenInfo[$type];
        if ($type == 'weibo') {
            $body = static::post($url, 'query', $data);
        } else {
            $body = static::get($url, 'query', $data);
        }
        $openid = Arr::get($body, 'openid') ?: Arr::get($body, 'uid');
        return $openid;
    }

    public static function getUserInfo($type, $openId, $token)
    {
        $static = new static;

        return $static->{$type . 'Info'}($openId, $token);
    }

    public static function getConfig($type, $key)
    {
        return config('laravel-oauth.' . $type . '.' . $key);
    }

    public static function getAppInfo($type)
    {
        return [self::getConfig($type, 'id'), self::getConfig($type, 'key')];
    }

    /**
     * @param $type
     * @return string
     */
    public static function getRedirctUrl($type)
    {
        $state = csrf_token();

        $redirect = \Request::get('redirect_uri', url(env('API_PREFIX', '') . "/OAuth/{$type}"));

        list($clientId, $key) = self::getAppInfo($type);

        $data = [
            'client_id'     => $clientId,
            'appid'         => $clientId,
            'redirect_uri'  => $redirect,
            'response_type' => 'code',
            'scope'         => 'snsapi_login',
            'state'         => $state,
        ];

        $query = http_build_query($data);
        if ($type == 'weixin') {
            $query .= '#wechat_redirect';
        }
        $url = static::$authUrls[$type] . '?' . $query;
        return $url;
    }

    public function qqInfo($openId, $token)
    {
        $data = self::requestUserInfo('qq', $openId, $token);

        return [
            'nickname' => Arr::get($data, 'nickname'),
            'avatar'   => Arr::get($data, 'figureurl_qq_1'),
            'info'     => $data
        ];
    }

    public static function requestUserInfo($type, $openId, $token)
    {
        list($clientId, $secret) = self::getAppInfo($type);

        $data = [
            'access_token' => $token,
            'openid'       => $openId,
            'uid'          => $openId,
            'appid'        => $clientId
        ];
        $url = static::$userInfo[$type];

        return static::get($url, 'query', $data);
    }

    public function weiboInfo($uid, $token)
    {
        $data = self::requestUserInfo('weibo', $uid, $token);
        return [
            'nickname' => Arr::get($data, 'screen_name'),
            'avatar'   => Arr::get($data, 'profile_image_url'),
            'info'     => $data
        ];
    }

    public function weixinInfo($openId, $token)
    {
        $data = self::requestUserInfo('weixin', $openId, $token);
        return [
            'nickname' => Arr::get($data, 'nickname'),
            'avatar'   => Arr::get($data, 'headimgurl'),
            'info'     => $data
        ];
    }
}
