<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2017/12/5
 * Time: 下午2:37
 */

namespace CaoJiayuan\LaravelApi\Aliyun\Sts;


use Sts\AssumeRoleRequest;
use Sts\Core\DefaultAcsClient;
use Sts\Core\Http\HttpHelper;
use Sts\Core\Profile\DefaultProfile;
use Sts\Core\Regions\EndpointProvider;

class StsAuth
{

    public static function auth($roleName, $regionId = "cn-hangzhou", $maxRetryNumber = 3)
    {
        $re = new AssumeRoleRequest();
        $accessKeyID = config('aliyun_sts.key');
        $accessKeySecret = config('aliyun_sts.secret');
        $roleArn = config('aliyun_sts.role_arn');
        $tokenExpire = config('aliyun_sts.expire_time');
        $policy = config('aliyun_sts.policy');
        $pf = DefaultProfile::getProfile($regionId, $accessKeyID, $accessKeySecret);
        $cli = new DefaultAcsClient($pf);
        $re->setRoleSessionName($roleName);
        $re->setRoleArn($roleArn);
        $re->setPolicy(json_encode($policy));
        $re->setDurationSeconds($tokenExpire);
        $response = $cli->doAction($re, null, null, false);
        $retryTimes = 0;
        $domain = EndpointProvider::findProductDomain($re->getRegionId(), $re->getProduct());

        while (500 <= $response->getStatus() && $retryTimes < $maxRetryNumber) {
            $requestUrl = $re->composeUrl(null, null, $domain);
            $response = HttpHelper::curl($requestUrl, null, $re->getHeaders());
            $retryTimes ++;
        }

        $result = json_decode($response->getBody(), true);

        $status = $response->getStatus();
        if ($status != 200) {
            throw new StsAuthException($result, $status, array_get($result, 'Message'));
        }

        return $result;
    }
}
