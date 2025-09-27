<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiCheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return response()->json([
                "status" => "error",
                "message" => "Anda belum ter-Authentikasi"
            ], 40);
        }

        $user = Auth::user();
        if (!in_array($user->role, $roles)) {
            return response()->json([
                "status" => "error",
                "message" => "Anda tidak memiliki akses"
            ], 403);
        }

        return $next($request);
    }
}
