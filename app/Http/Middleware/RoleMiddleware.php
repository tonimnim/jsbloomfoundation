<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
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
        $requiredRole = UserRole::from($role);

        // Check if user has the required role
        if ($user->role !== $requiredRole) {
            abort(403, 'Unauthorized. You do not have permission to access this resource.');
        }

        // Update last login info for admins
        if ($user->isAdmin()) {
            $user->updateLastLogin();
        }

        return $next($request);
    }
}