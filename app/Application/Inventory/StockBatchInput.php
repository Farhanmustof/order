<?php

namespace App\Application\Inventory;

use App\Application\Contracts\Dates;
use App\Domain\Shared\BusinessRuleException;

final class StockBatchInput
{
    public function __construct(
        public readonly int $rawMaterialId,
        public readonly ?int $supplierId,
        public readonly ?string $batchCode,
        public readonly string $receivedDate,
        public readonly ?string $expiryDate,
        public readonly float $quantity,
        public readonly float $unitCost,
        public readonly ?string $notes,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            rawMaterialId: (int) $data['raw_material_id'],
            supplierId: isset($data['supplier_id']) && $data['supplier_id'] !== '' ? (int) $data['supplier_id'] : null,
            batchCode: $data['batch_code'] ?? null,
            receivedDate: (string) $data['received_date'],
            expiryDate: $data['expiry_date'] ?? null,
            quantity: (float) $data['quantity'],
            unitCost: (float) ($data['unit_cost'] ?? 0),
            notes: $data['notes'] ?? null,
        );
    }

    /** @return array{0: \DateTimeImmutable, 1: ?\DateTimeImmutable} */
    public function dates(): array
    {
        $received = Dates::required($this->receivedDate, 'Tanggal masuk');
        $expiry = Dates::parse($this->expiryDate, 'Tanggal kedaluwarsa');
        if ($expiry !== null && $expiry < $received) {
            throw new BusinessRuleException('Tanggal kedaluwarsa tidak boleh sebelum tanggal masuk.');
        }
        if ($this->quantity <= 0) {
            throw new BusinessRuleException('Jumlah stok masuk harus lebih dari 0.');
        }
        if ($this->unitCost < 0) {
            throw new BusinessRuleException('Harga beli tidak boleh negatif.');
        }

        return [$received, $expiry];
    }
}
