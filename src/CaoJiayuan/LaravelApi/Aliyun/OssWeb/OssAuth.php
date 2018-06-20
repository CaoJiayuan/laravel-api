<?php

/**
 * Get oss auth policy, for wechat mini program
 */

namespace CaoJiayuan\LaravelApi\Aliyun\OssWeb;


use DateTime;

class OssAuth
{

    public static function policy($id, $key, $bucket, $endPoint, $expire = 600, $dir = null)
    {
        $host = "https://$bucket.$endPoint";

        $now = time();
        $end = $now + $expire;
        $expiration = static::gmtIso8601($end);

        $dir || $dir = 'upload/' . date('Y-m-d') . '/';

        $condition = [0 => 'content-length-range', 1 => 0, 2 => 1048576000];
        $conditions[] = $condition;

        $start = [0 => 'starts-with', 1 => '$key', 2 => $dir];
        $conditions[] = $start;

        $arr = ['expiration' => $expiration, 'conditions' => $conditions];

        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));

        $response = [];
        $response['accessid'] = $id;
        $response['OSSAccessKeyId'] = $id;
        $response['host'] = $host;
        $response['policy'] = $base64_policy;
        $response['signature'] = $signature;
        $response['expire'] = $end;
        $response['dir'] = $dir;

        return $response;
    }

    public static function gmtIso8601($time) {
        $dtStr = date("c", $time);
        $dateTime = new DateTime($dtStr);
        $expiration = $dateTime->format(DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration."Z";
    }
}
