<?php

namespace App\Domain\Catalog;

use App\Domain\Shared\BusinessRuleException;

final class SupplierDetails
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $phone,
        public readonly ?string $address,
        public readonly ?string $notes,
    ) {
        if (trim($name) === '') {
            throw new BusinessRuleException('Nama supplier wajib diisi.');
        }
    }
}
