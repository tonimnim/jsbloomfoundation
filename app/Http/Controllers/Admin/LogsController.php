<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use App\Models\ApplicationLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LogsController extends Controller
{
    /**
     * Display system logs.
     */
    public function index(): Response
    {
        // Get access logs from database
        $accessLogs = $this->getAccessLogs();
        
        // Get application logs from database
        $applicationLogs = $this->getApplicationLogs();
        
        // Calculate stats
        $stats = $this->calculateStats();

        return Inertia::render('Admin/Logs/Index', [
            'accessLogs' => $accessLogs,
            'applicationLogs' => $applicationLogs,
            'stats' => $stats,
        ]);
    }

    /**
     * Get access logs from database.
     */
    private function getAccessLogs(): array
    {
        return AccessLog::with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'method' => $log->method,
                    'url' => $log->url,
                    'route_name' => $log->route_name,
                    'status_code' => $log->status_code,
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                    'response_time' => $log->response_time,
                    'user' => $log->user ? [
                        'name' => $log->user->name,
                        'email' => $log->user->email,
                    ] : null,
                    'created_at' => $log->created_at,
                ];
            })
            ->toArray();
    }

    /**
     * Get application logs from database.
     */
    private function getApplicationLogs(): array
    {
        return ApplicationLog::orderBy('logged_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'level' => $log->level,
                    'channel' => $log->channel,
                    'message' => $log->message,
                    'context' => is_array($log->context) && !empty($log->context) 
                        ? json_encode($log->context, JSON_PRETTY_PRINT) 
                        : null,
                    'file' => $log->file,
                    'line' => $log->line,
                    'created_at' => $log->logged_at,
                ];
            })
            ->toArray();
    }

    /**
     * Calculate statistics from access logs.
     */
    private function calculateStats(): array
    {
        $totalRequests = AccessLog::count();
        $successfulRequests = AccessLog::successful()->count();
        $errorRequests = AccessLog::errors()->count();
        $uniqueVisitors = AccessLog::distinct('ip_address')->count();

        return [
            'total_requests' => $totalRequests,
            'successful_requests' => $successfulRequests,
            'error_requests' => $errorRequests,
            'unique_visitors' => $uniqueVisitors,
        ];
    }


    /**
     * Clear old logs (maintenance function).
     */
    public function clearOldLogs(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $cutoffDate = now()->subDays($request->days);
        
        $accessLogsDeleted = AccessLog::where('created_at', '<', $cutoffDate)->delete();
        $appLogsDeleted = ApplicationLog::where('logged_at', '<', $cutoffDate)->delete();
        
        $totalDeleted = $accessLogsDeleted + $appLogsDeleted;

        ApplicationLog::info("Cleared {$totalDeleted} log entries older than {$request->days} days", 'admin', [
            'days' => $request->days,
            'access_logs_deleted' => $accessLogsDeleted,
            'application_logs_deleted' => $appLogsDeleted,
        ]);

        return back()->with('success', "Cleared {$totalDeleted} old log entries ({$accessLogsDeleted} access logs, {$appLogsDeleted} application logs).");
    }
}