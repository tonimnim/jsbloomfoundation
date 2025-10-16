<?php

namespace App\Http\Middleware;

use App\Models\AccessLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAccessMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        // Only log if not in testing environment and not API routes
        if (!app()->environment('testing') && !$request->is('api/*')) {
            try {
                AccessLog::logRequest($request, $response, $startTime);
            } catch (\Exception $e) {
                // Fail silently to not break the application
                \Log::error('Failed to log access request: ' . $e->getMessage());
            }
        }

        return $response;
    }
}