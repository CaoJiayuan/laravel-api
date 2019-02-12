<?php

namespace CaoJiayuan\LaravelApi\Http\Middleware;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

/**
 * Response wrapper for api
 * Class ResponseWrapper
 * @package App\Http\Middleware
 *
 */
class ResponseWrapper
{
    protected $wrapper = 'data';

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var Response $response */
        $response = $next($request);

        if ($this->shouldConvert($request, $response)) {
            return $this->convertResponse($response, $request);
        }

        return $response;
    }

    /**
     * @param Response $response
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|Response
     */
    protected function convertResponse($response, $request)
    {
        $origin = $response->getOriginalContent();
        $additional = $this->getAdditional($response, $request);
        $status = $response->status();
        $headers = $response->headers;

        if (is_array($origin) || $origin instanceof Arrayable) {
            JsonResource::$wrap = $this->wrapper;
            return (new JsonResource($origin))->additional($additional)
                ->toResponse($request)
                ->setStatusCode($status)
                ->withHeaders($headers);
        } else {
            $jsonResponse = new JsonResponse(array_merge([
                $this->wrapper => $origin
            ], $additional), $status);

            $jsonResponse->withHeaders($headers)
                ->header('Content-type', 'application/json');

            return $jsonResponse;
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return bool
     */
    protected function shouldConvert($request, $response)
    {
        return $request->expectsJson() && $response->isSuccessful();
    }

    /**
     * @param Response $response
     * @param Request $request
     * @return array
     */
    protected function getAdditional($response, $request)
    {
        return [
            'code' => $response->getStatusCode()
        ];
    }
}
