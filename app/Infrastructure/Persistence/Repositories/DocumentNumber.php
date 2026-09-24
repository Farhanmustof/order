<?php

namespace App\Infrastructure\Persistence\Repositories;

use Illuminate\Database\Eloquent\Model;

/** Membuat nomor dokumen berurutan per bulan, contoh: PO-202609-0001. */
final class DocumentNumber
{
    /** @param class-string<Model> $model */
    public static function next(string $model, string $prefix): string
    {
        $base = $prefix.'-'.now()->format('Ym').'-';
        $last = $model::withTrashed()
            ->where('number', 'like', $base.'%')
            ->orderByDesc('number')
            ->lockForUpdate()
            ->value('number');
        $sequence = $last ? ((int) substr($last, strlen($base))) + 1 : 1;

        return $base.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
