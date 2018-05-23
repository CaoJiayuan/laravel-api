<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/5/23
 * Time: 下午4:56
 */

namespace CaoJiayuan\LaravelApi\Utils;

use Psr\Http\Message\ResponseInterface;

class ResponseParser
{

    public static function parse(ResponseInterface $response)
    {
        $contentType = $response->getHeaderLine('Content-Type');
        $body = $response->getBody();
        if (static::strContains($contentType, '/json')) {
            return json_decode($body, true);
        }
        if (static::strContains($contentType, '/xml')) {
            return xml_to_array($body);
        }

        return $body;
    }

    public static function strContains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}
