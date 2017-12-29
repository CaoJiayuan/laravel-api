<?php

namespace CaoJiayuan\LaravelApi\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectWrapper
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
      $response = $next($request);

      if ($response instanceof RedirectResponse && $request->expectsJson()) {

        $targetUrl = $response->getTargetUrl();
        return response()->json(['code' => 302, 'message' => 'Need redirect', 'url' => $targetUrl], 302);
      }

      return $response;
    }
}
