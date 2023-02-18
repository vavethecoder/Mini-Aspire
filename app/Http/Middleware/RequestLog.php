<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RequestLog
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->hasHeader('X-Correlation-ID')) {
            $request->headers->set('X-Correlation-ID', (string)Str::uuid());
        }

        Log::channel('request')->info('New request received for url ' . url()->current() . ' with Correlation ID : ' . $request->header('X-Correlation-ID'));
        return $next($request)->header('X-Correlation-ID', $request->header('X-Correlation-ID'));
    }


    public function terminate(Request $request, $response)
    {
        Log::channel('request')->info('Request processed for url ' . url()->current() . ' with Correlation ID : ' . $request->header('X-Correlation-ID'));
    }
}



