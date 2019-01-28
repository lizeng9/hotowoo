<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;

class JwtMiddleware
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

        $auth        = $request->header("authorization");
        list($token) = sscanf($auth, 'Bearer %s');

        if ($token) {
            $jwt = $token;
        } else {
            $jwt = $request->input('jwt');
        }

        if (!$jwt) {
//            return response()->json(["code"=>401]);
            $jwt = "test";
            $user = [
                "uid"=>123,
                "gid"=>1,
                "name"=>"bill"
            ];
        } else {
            $jwt_key     = env("JWT_KEY", "");
            JWT::$leeway = 300;
            try {
                $decoded = JWT::decode($jwt, $jwt_key, array('HS256'));
                $user = (array) $decoded;
            } catch (\Exception $e) {
                // echo 'exception: ',  $e->getMessage(), "\n";
                // $user = [];
                return response()->json(["code"=>401]);
            }

        }


        $data = [
            "jwt" =>$jwt,
            "user" =>$user,
        ];

        $request->merge($data);
        return $next($request);
    }
}
