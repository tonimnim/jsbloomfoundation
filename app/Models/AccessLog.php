<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'method',
        'url',
        'route_name',
        'status_code',
        'ip_address',
        'user_agent',
        'referer',
        'response_time',
        'request_data',
        'headers',
    ];

    protected $casts = [
        'request_data' => 'array',
        'headers' => 'array',
        'response_time' => 'decimal:3',
    ];

    /**
     * Get the user that made this request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an access request.
     */
    public static function logRequest($request, $response, $startTime = null): void
    {
        $responseTime = $startTime ? (microtime(true) - $startTime) * 1000 : null;

        static::create([
            'user_id' => auth()->id(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route_name' => $request->route()?->getName(),
            'status_code' => $response->getStatusCode(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'response_time' => $responseTime,
            'request_data' => $request->method() !== 'GET' ? $request->except(['password', 'password_confirmation', '_token']) : null,
            'headers' => collect($request->headers->all())->except(['authorization', 'cookie'])->toArray(),
        ]);
    }

    /**
     * Scope for recent logs.
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope for specific status codes.
     */
    public function scopeWithStatus($query, $statusCode)
    {
        return $query->where('status_code', $statusCode);
    }

    /**
     * Scope for errors (4xx and 5xx).
     */
    public function scopeErrors($query)
    {
        return $query->where('status_code', '>=', 400);
    }

    /**
     * Scope for successful requests (2xx).
     */
    public function scopeSuccessful($query)
    {
        return $query->whereBetween('status_code', [200, 299]);
    }
}