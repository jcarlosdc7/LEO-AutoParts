<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public static function record(
        string $event,
        ?Model $model = null,
        array $old = [],
        array $new = [],
        ?int $actorId = null,
    ): void {
        AuditLog::create([
            'user_id' => $actorId ?? auth()->id(),
            'event' => $event,
            'auditable_type' => $model ? $model::class : null,
            'auditable_id' => $model?->getKey(),
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}
