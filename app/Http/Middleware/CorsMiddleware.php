<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
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

        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        } else {
            $response = $next($request);
        }


        $response->headers->set('Access-Control-Allow-Methods', 'HEAD, GET, POST, PUT, PATCH, DELETE');
        $response->headers->set('Access-Control-Allow-Headers', $response->headers->get('Access-Control-Request-Headers'));
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $now = time();
        $response->headers->set('Last-modified', gmdate('D, d M Y H:i:s T', $now));

        return $response;
    }

}
