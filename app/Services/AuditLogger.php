<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    public function record(
        string $action,
        string $module,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): void {
        AuditLog::query()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => $module,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
