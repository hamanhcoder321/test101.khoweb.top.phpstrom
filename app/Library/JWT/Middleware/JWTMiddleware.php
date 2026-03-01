<?php
namespace App\Library\JWT\Middleware;

use Closure;
use App\Library\JWT\Facades\JWTAuth;
use Exception;

class JWTMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken();
            $request->attributes->add(['currentUser' => $user]);
            if(!$user) return response()->json(['msg'=>'Unauthorized'], 401);
        } catch(Exception $e) {
            return response()->json(['msg'=>$e->getMessage()], 401);
        }
        return $next($request);
    }
}
