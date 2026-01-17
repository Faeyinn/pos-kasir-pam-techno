<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // If user is Master
        if ($user->role === 'master') {
            // Allow access to logout even without role selection
            if ($request->routeIs('logout')) {
                return $next($request);
            }

            $activeRole = session('active_role');
            
            // If we are currently ON the selection page or setting the role, allow it to proceed
            // Note: We'll likely not put this middleware on the selection route itself, but just in case.
            if ($request->routeIs('role.selection') || $request->routeIs('role.set')) {
               return $next($request);
            }

            if (!$activeRole) {
                return redirect()->route('role.selection');
            }

            // specific check: if we need 'admin' but active is 'kasir', block/redirect
            if ($role === 'admin' && $activeRole !== 'admin') {
                 return redirect()->route('kasir');
            }
            
            // if we need 'kasir' but active is 'admin', block/redirect
            if ($role === 'kasir' && $activeRole !== 'kasir') {
                 return redirect()->route('admin.dashboard');
            }

            return $next($request);
        }

        // specific check for regular users
        if ($user->role !== $role) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
