<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogger
{
    public function log(Request $request, string $module, string $action, mixed $recordId = null, array $old = [], array $new = []): void
    {
        $user = $request->user();

        AuditLog::create([
            'company_id' => $user?->company_id,
            'branch_id' => $user?->branch_id,
            'user_id' => $user?->id,
            'module' => $module,
            'action' => $action,
            'record_id' => is_numeric($recordId) ? (int) $recordId : null,
            'auditable_type' => $module,
            'auditable_id' => is_numeric($recordId) ? (int) $recordId : null,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);
    }
}