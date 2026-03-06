<?php

namespace App\Services;

use App\Models\AuditEvent;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public function log(
        string $entityType,
        int $entityId,
        string $action,
        ?array $before = null,
        ?array $after = null
    ): void {
        AuditEvent::create([
            'actor_user_id' => Auth::id(),
            'entity_type'   => $entityType,
            'entity_id'     => $entityId,
            'action'        => $action,
            'before_json'   => $before,
            'after_json'    => $after,
            'created_at'    => now(),
        ]);
    }
}