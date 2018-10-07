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

    public static function auth($roleName, $regionId = "cn-hangzhou", $maxRetries = 3, $options = [])
    {
        $re = new AssumeRoleRequest();
        $accessKeyID = array_get($options, 'key', config('aliyun_sts.key'));
        $accessKeySecret = array_get($options, 'secret', config('aliyun_sts.secret'));
        $roleArn = array_get($options, 'role_arn', config('aliyun_sts.role_arn'));
        $tokenExpire = array_get($options, 'expire_time', config('aliyun_sts.expire_time'));
        $policy = array_get($options, 'policy', config('aliyun_sts.policy'));
        $pf = DefaultProfile::getProfile($regionId, $accessKeyID, $accessKeySecret);
        $cli = new DefaultAcsClient($pf);
        $re->setRoleSessionName($roleName);
        $re->setRoleArn($roleArn);
        $re->setPolicy(json_encode($policy));
        $re->setDurationSeconds($tokenExpire);
        $response = $cli->doAction($re, null, null, false);
        $retries = 0;
        $domain = EndpointProvider::findProductDomain($re->getRegionId(), $re->getProduct());

        while (500 <= $response->getStatus() && $retries < $maxRetries) {
            $requestUrl = $re->composeUrl(null, null, $domain);
            $response = HttpHelper::curl($requestUrl, null, $re->getHeaders());
            $retries++;
        }

        $result = json_decode($response->getBody(), true);

        $status = $response->getStatus();
        if ($status != 200) {
            throw new StsAuthException($result, $status, array_get($result, 'Message'));
        }

        return $result;
    }
}
