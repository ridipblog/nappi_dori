<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;

class ProtectAPIMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $api_key = $request->header('api_key');
            $api_key = $api_key ? Crypt::decryptString($api_key) : '';
            if ($api_key !== env('API_KEY')) {
                return response()->json(['status' => 400, 'message' => "API key mismatched !"]);
            }
        } catch (Exception $err) {
            return response()->json(['status' => 400, 'message' => "Server error at API protection !"]);
        }
        return $next($request);
    }
}
