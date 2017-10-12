<?php

namespace Jsdecena\LPM\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class ProviderDetectorMiddleware extends ServiceProvider
{
    /**
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        if (array_key_exists('provider', $request->all())) {
            config(['auth.guards.api.provider' => $request->input('provider')]);
        }

        return $next($request);
    }
}