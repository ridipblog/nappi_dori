<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,$guard='user_guard'): Response
    {
        $auth_res = [
            'message' => null,
            'status' => 400
        ];
        try {
            if (!$request->user('user_guard') || $request->user($guard)->active != 1) {
                return response()->json(['message' => 'Your are not logged in ', 'status' => 400]);
            }
        } catch (Exception $err) {
            return response()->json(['message' => 'Server error please try later !', 'status' => 400]);
        }
        return $next($request);
    }
}
