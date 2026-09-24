<?php

namespace App\Application\Maintenance;

use App\Application\Contracts\AuditLogger;
use App\Application\Contracts\RecycleBin;
use App\Application\Contracts\TransactionManager;
use App\Domain\Shared\BusinessRuleException;

final class RestoreDeletedRecord
{
    public function __construct(
        private RecycleBin $bin,
        private TransactionManager $tx,
        private AuditLogger $audit,
    ) {
    }

    public function execute(string $type, int $id, int $actorId): string
    {
        if (! array_key_exists($type, $this->bin->types())) {
            throw new BusinessRuleException('Jenis data tidak dikenal.');
        }

        return $this->tx->run(function () use ($type, $id, $actorId) {
            $label = $this->bin->restore($type, $id);
            $this->audit->log($actorId, 'restore', $type, $id, "Memulihkan {$this->bin->types()[$type]} {$label}");

            return $label;
        });
    }
}
