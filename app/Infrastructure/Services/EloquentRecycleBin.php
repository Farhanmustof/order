<?php

namespace App\Infrastructure\Services;

use App\Application\Contracts\RecycleBin;
use App\Domain\Shared\NotFoundException;
use App\Infrastructure\Persistence\Models;
use Illuminate\Database\Eloquent\Model;

final class EloquentRecycleBin implements RecycleBin
{
    /** [kunci => [kelas model, label, kolom nama]] */
    public const MAP = [
        'pre_order' => [Models\PreOrder::class, 'Pre-order', 'number'],
        'production_plan' => [Models\ProductionPlan::class, 'Rencana produksi', 'number'],
        'stock_batch' => [Models\StockBatch::class, 'Stok masuk', 'batch_code'],
        'product' => [Models\Product::class, 'Produk', 'name'],
        'raw_material' => [Models\RawMaterial::class, 'Bahan baku', 'name'],
        'supplier' => [Models\Supplier::class, 'Supplier', 'name'],
    ];

    public function types(): array
    {
        return array_map(fn ($row) => $row[1], self::MAP);
    }

    public function restore(string $type, int $id): string
    {
        [$class, $label, $column] = self::MAP[$type];
        /** @var Model $record */
        $record = $class::onlyTrashed()->find($id) ?? throw NotFoundException::of($label.' terhapus');
        $record->restore();

        return (string) ($record->{$column} ?: "#{$id}");
    }

    /** Daftar data terhapus untuk halaman "Data terhapus". */
    public function listing(string $type)
    {
        [$class] = self::MAP[$type];

        return $class::onlyTrashed()->latest('deleted_at')->paginate(25)->withQueryString();
    }
}
