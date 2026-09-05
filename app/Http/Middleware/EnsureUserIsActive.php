<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isActive()) {
            if ($request->is('api/*') || $request->expectsJson()) {
                if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
                    $user->currentAccessToken()->delete();
                }

                return response()->json([
                    'message' => __('auth.inactive'),
                ], 403);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['login' => __('auth.inactive')]);
        }

        return $next($request);
    }
}
