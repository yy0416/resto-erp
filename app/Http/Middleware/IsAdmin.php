<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // 🎯 核心修正：手动导入官方 Auth 门面

class IsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 🎯 现在的 Auth::check() 绝对不会再报错了
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Accès interdit. Zone Admin uniquement.'], 403);
            }
            abort(403, 'Accès interdit.');
        }

        return $next($request);
    }
}
