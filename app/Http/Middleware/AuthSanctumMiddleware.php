<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class AuthSanctumMiddleware
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
        $authorization = $request->header('Authorization');

        if(!$authorization) 
        {
            return response()->json(['error' => 'Token missing'], 401);
        }

        // Extract token after "Bearer"
        $token = str_replace('Bearer ', '', $authorization);

        // Validate token using Sanctum
        $accessToken = PersonalAccessToken::findToken($token);

        if(!$accessToken) 
        {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }

        // Attach user to the request
        $request->user = $accessToken->tokenable;

        return $next($request);
    }

}