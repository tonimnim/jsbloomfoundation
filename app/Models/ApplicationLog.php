<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'channel',
        'message',
        'context',
        'extra',
        'file',
        'line',
        'class',
        'function',
        'logged_at',
    ];

    protected $casts = [
        'context' => 'array',
        'extra' => 'array',
        'logged_at' => 'datetime',
    ];

    /**
     * Log levels in order of severity.
     */
    const LEVELS = [
        'emergency' => 800,
        'alert' => 700,
        'critical' => 600,
        'error' => 500,
        'warning' => 400,
        'notice' => 300,
        'info' => 200,
        'debug' => 100,
    ];

    /**
     * Log an application event.
     */
    public static function logEvent(string $level, string $message, string $channel = 'app', array $context = [], array $extra = []): void
    {
        // Get backtrace information
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $trace[1] ?? [];

        static::create([
            'level' => strtolower($level),
            'channel' => $channel,
            'message' => $message,
            'context' => $context,
            'extra' => $extra,
            'file' => $caller['file'] ?? null,
            'line' => $caller['line'] ?? null,
            'class' => $caller['class'] ?? null,
            'function' => $caller['function'] ?? null,
            'logged_at' => now(),
        ]);
    }

    /**
     * Scope for specific log level.
     */
    public function scopeLevel($query, string $level)
    {
        return $query->where('level', strtolower($level));
    }

    /**
     * Scope for specific channel.
     */
    public function scopeChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope for errors and above (error, critical, alert, emergency).
     */
    public function scopeErrors($query)
    {
        return $query->whereIn('level', ['error', 'critical', 'alert', 'emergency']);
    }

    /**
     * Scope for recent logs.
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('logged_at', '>=', now()->subHours($hours));
    }

    /**
     * Get the severity score for the log level.
     */
    public function getSeverityAttribute(): int
    {
        return static::LEVELS[$this->level] ?? 0;
    }

    /**
     * Helper methods for different log levels.
     */
    public static function emergency(string $message, string $channel = 'app', array $context = []): void
    {
        static::logEvent('emergency', $message, $channel, $context);
    }

    public static function alert(string $message, string $channel = 'app', array $context = []): void
    {
        static::logEvent('alert', $message, $channel, $context);
    }

    public static function critical(string $message, string $channel = 'app', array $context = []): void
    {
        static::logEvent('critical', $message, $channel, $context);
    }

    public static function error(string $message, string $channel = 'app', array $context = []): void
    {
        static::logEvent('error', $message, $channel, $context);
    }

    public static function warning(string $message, string $channel = 'app', array $context = []): void
    {
        static::logEvent('warning', $message, $channel, $context);
    }

    public static function notice(string $message, string $channel = 'app', array $context = []): void
    {
        static::logEvent('notice', $message, $channel, $context);
    }

    public static function info(string $message, string $channel = 'app', array $context = []): void
    {
        static::logEvent('info', $message, $channel, $context);
    }

    public static function debug(string $message, string $channel = 'app', array $context = []): void
    {
        static::logEvent('debug', $message, $channel, $context);
    }
}