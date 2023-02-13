<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasAdminPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth('api')->user();
        if(!is_null($user) && $user->isAdmin()) {
            return $next($request);
        }
        return response([config('enums.api_status')['UNAUTHORIZED']],401);
    }
}
