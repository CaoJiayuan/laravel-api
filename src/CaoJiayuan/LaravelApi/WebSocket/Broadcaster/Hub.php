<?php
/**
 * Created by PhpStorm.
 * User: cjy
 * Date: 2018/4/8
 * Time: 下午4:05
 */

namespace CaoJiayuan\LaravelApi\WebSocket\Broadcaster;


use CaoJiayuan\LaravelApi\Helpers\JsonHelper;
use Workerman\Connection\TcpConnection;

class Hub
{
    use JsonHelper;

    protected static $subscribes = [];// [{channel: [subscriberId, ...]}]

    protected static $subscribers = []; // [{subscriberId: resolver}]

    public static function subscribe($channels, $subscriber)
    {
        if (!is_array($channels)) {
            $channels = [$channels];
        }
        foreach ($channels as $channel) {
            if (empty(static::$subscribes[$channel])) {
                static::$subscribes[$channel] = [];
            }
            $subId = self::formatSubscriber($subscriber);

            if (!in_array($subId, static::$subscribes[$channel])) {
                array_push(static::$subscribes[$channel], $subId);
            }
        }
    }

    public static function unSubscribe($channels, $subscriber)
    {
        $subId = self::getSubscriberId($subscriber);

        if (!is_array($channels)) {
            $channels = [$channels];
        }
        foreach ($channels as $channel) {
            if (!empty(static::$subscribes[$channel])) {
                $subIds = static::$subscribes[$channel];
                static::$subscribes[$channel] = array_filter($subIds, function ($id) use ($subId) {
                    return $id != $subId;
                });
            }
        }
    }

    public static function formatSubscriber($subscriber)
    {
        $id = self::getSubscriberId($subscriber);

        $sub = function ($payload) use ($subscriber, $id) {
            if ($payload === null) {
                return $payload;
            }
            if (static::shouldBeJson($payload)) {
                $payload = static::morphToJson($payload);
            }
            if ($subscriber instanceof TcpConnection) {
                return $subscriber->send($payload);
            }
            if ($subscriber instanceof Subscriber) {
                return $subscriber->notification($payload);
            }
            return $payload;
        };

        static::$subscribers[$id] = $sub;
        return $id;
    }

    public static function getSubscriberId($subscriber)
    {
        if ($subscriber instanceof TcpConnection) {
            return $subscriber->id;
        }

        if ($subscriber instanceof Subscriber) {
            return $subscriber->getId();
        }
        $expect = Subscriber::class;
        $giving = get_class($subscriber);

        throw new \InvalidArgumentException("Argument [subscriber] expect instance of {$expect}, {$giving} giving.");
    }

    public static function dispatch($channels, $payload, $subscriber, $once = false)
    {
        if (!is_array($channels)) {
            $channels = [$channels];
        }
        foreach ($channels as $channel) {
            if (isset(static::$subscribes[$channel])) {
                $subIds = static::$subscribes[$channel];
                $subscriberId = self::getSubscriberId($subscriber);
                if (in_array($subscriberId, $subIds)) { // Is channel subscriber
                    foreach ($subIds as $id) { // Send message
                        if (isset(static::$subscribers[$id])) {
                            $sub = static::$subscribers[$id];
                            $sub && $sub($payload);
                        }
                    }

                    if ($once) {
                        static::$subscribes[$channel] = array_filter($subIds, function ($id) use ($subscriberId) {
                            return $id != $subscriberId;
                        });
                    }
                }
            }
        }
    }

}
