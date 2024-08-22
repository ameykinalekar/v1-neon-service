<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiDocumentationAuthCheck
{
/**
 * Handle an incoming request.
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  \Closure(\Illuminate\Http\Request):
(\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
 */
    public function handle(Request $request, Closure $next)
    {
        dd(Auth()->user());

        // if auth User allow access to API
        return $next($request);
        // else redirect to Login route with auto redirect back

    }
}
