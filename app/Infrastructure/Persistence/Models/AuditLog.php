<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'action', 'entity', 'entity_id', 'description', 'ip_address'];

    public const ENTITY_LABELS = [
        'pre_order' => 'Pre-order',
        'production_plan' => 'Rencana produksi',
        'stock_batch' => 'Stok masuk',
        'stock_movement' => 'Stok keluar',
        'product' => 'Produk',
        'raw_material' => 'Bahan baku',
        'supplier' => 'Supplier',
        'user' => 'Akun',
        'auth' => 'Login',
    ];

    public const ACTION_LABELS = [
        'create' => 'Tambah',
        'update' => 'Ubah',
        'delete' => 'Hapus',
        'status' => 'Ubah status',
        'restore' => 'Pulihkan',
        'login' => 'Masuk',
        'logout' => 'Keluar',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
