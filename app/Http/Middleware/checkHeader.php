<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Response;

class checkHeader
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // dd($_SERVER);
        if (!isset($_SERVER['HTTP_X_NEON'])) {
            $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Please set neon header', 'data' => []]];
            // return Response::json(array('error' => 'Please set neon header'));
            return response()->json($reponse, 401);
        }

        if ($_SERVER['HTTP_X_NEON'] != 'UW9sYXJpc05lb25AMjAyNA==') {
            $reponse = ['status' => false, 'statuscode' => '401', 'result' => [], 'error' => ['message' => 'Wrong neon header', 'data' => []]];
            // return Response::json(array('error' => 'wrong neon header'));
            return response()->json($reponse, 401);
        }

        return $next($request);
    }

}
