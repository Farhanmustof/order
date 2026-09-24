<?php

namespace App\Infrastructure\Services;

use App\Application\Contracts\AuditLogger;
use App\Infrastructure\Persistence\Models\AuditLog;

final class EloquentAuditLogger implements AuditLogger
{
    public function log(int $actorId, string $action, string $entity, ?int $entityId, string $description): void
    {
        AuditLog::create([
            'user_id' => $actorId,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'description' => mb_substr($description, 0, 255),
            'ip_address' => request()?->ip(),
        ]);
    }
}
