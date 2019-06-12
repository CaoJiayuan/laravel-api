<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/2/23
 * Time: 上午9:46
 */

namespace CaoJiayuan\LaravelApi\Aliyun\Sts;


use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class SimpleStsController extends Controller
{
    public function auth()
    {
        $data = StsAuth::auth('role-'.str_random(5));
        $raw = Arr::get($data, 'Credentials');
        $expire = new Carbon(Arr::get($raw, 'Expiration')) ?: Carbon::now();
        $cre = [
            'accessKeyId'     => Arr::get($raw, 'AccessKeyId'),
            'accessKeySecret' => Arr::get($raw, 'AccessKeySecret'),
            'stsToken'        => Arr::get($raw, 'SecurityToken'),
            'expire_at'       => $expire->timestamp,
            'expire_date'     => $expire->toDateTimeString(),
            'endpoint'        => env('OSS_ENDPOINT'),
            'bucket'          => env('OSS_BUCKET'),
            'prefix'          => 'upload/' . date('Y-m-d'),
        ];

        return $cre;
    }
}
