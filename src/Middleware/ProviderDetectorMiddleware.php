<?php

namespace Jsdecena\LPM\Middleware;

use Illuminate\Http\Request;

class ProviderDetectorMiddleware
{
    /**
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $validator = validator()->make($request->all(), [
            'username' => 'required',
            'provider' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->getMessageBag(),
                'status_code' => 422
            ], 422);
        }

        config(['auth.guards.api.provider' => $request->input('provider')]);

        return $next($request);
    }
}