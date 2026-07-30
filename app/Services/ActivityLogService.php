<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    private const SENSITIVE_KEYS = [
        'password', 'password_confirmation', 'current_password', 'remember_token',
        'token', 'secret', 'api_key', 'access_token', 'mail_password',
    ];

    public static function log(
        string $module,
        string $action,
        string $description,
        ?Model $subject = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null,
    ): ActivityLog {
        $request = request();

        return ActivityLog::create([
            'user_id' => $userId ?? auth()->id(),
            'module' => mb_strtolower(trim($module)),
            'action' => mb_strtolower(trim($action)),
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'description' => mb_substr(strip_tags($description), 0, 500),
            'old_values' => self::sanitize($oldValues),
            'new_values' => self::sanitize($newValues),
            'ip_address' => $request?->ip(),
            'user_agent' => mb_substr((string) $request?->userAgent(), 0, 1000) ?: null,
            'created_at' => now(),
        ]);
    }

    private static function sanitize(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        $walk = function (array $data) use (&$walk): array {
            return collect($data)->mapWithKeys(function ($value, $key) use (&$walk) {
                if (in_array(mb_strtolower((string) $key), self::SENSITIVE_KEYS, true)) {
                    return [$key => '[REDACTED]'];
                }

                return [$key => is_array($value) ? $walk($value) : $value];
            })->all();
        };

        return $walk($values);
    }
}
