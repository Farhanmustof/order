<?php

namespace App\Application\Contracts;

interface AuditLogger
{
    public function log(int $actorId, string $action, string $entity, ?int $entityId, string $description): void;
}
