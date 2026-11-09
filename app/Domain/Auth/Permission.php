<?php

namespace App\Domain\Auth;

/**
 * Hak akses per menu. Setiap menu punya hak "lihat" dan/atau "kelola".
 * Nilai (value) dipakai di route (middleware can:...) dan tampilan (@can('...')).
 */
enum Permission: string
{
    case PreOrderView = 'pre-order.lihat';
    case PreOrderManage = 'pre-order.kelola';
    case ProductionView = 'produksi.lihat';
    case ProductionManage = 'produksi.kelola';
    case StockView = 'stok.lihat';
    case StockManage = 'stok.kelola';
    case PurchaseView = 'pembelian.lihat';
    case PurchaseManage = 'pembelian.kelola';
    case ProductView = 'produk.lihat';
    case ProductManage = 'produk.kelola';
    case SupplierView = 'supplier.lihat';
    case SupplierManage = 'supplier.kelola';
    case UserManage = 'pengguna.kelola';
    case AuditView = 'riwayat-aktivitas.lihat';
    case TrashManage = 'data-terhapus.kelola';

    /**
     * Daftar menu untuk tabel centang di form akun.
     * Format: nama menu => [hak lihat, hak kelola]. null = kolom itu tidak ada.
     *
     * @return array<string, array{0: ?Permission, 1: ?Permission}>
     */
    public static function menus(): array
    {
        return [
            'Pre-order' => [self::PreOrderView, self::PreOrderManage],
            'Rencana produksi' => [self::ProductionView, self::ProductionManage],
            'Stok bahan baku & riwayat stok' => [self::StockView, self::StockManage],
            'Pemesanan ke supplier' => [self::PurchaseView, self::PurchaseManage],
            'Produk, resep & bahan baku' => [self::ProductView, self::ProductManage],
            'Supplier' => [self::SupplierView, self::SupplierManage],
            'Pengguna' => [null, self::UserManage],
            'Riwayat aktivitas' => [self::AuditView, null],
            'Data terhapus' => [null, self::TrashManage],
        ];
    }

    /** Hak "lihat" yang otomatis ikut bila hak "kelola" ini dicentang. */
    public function viewPair(): ?self
    {
        foreach (self::menus() as [$view, $manage]) {
            if ($manage === $this) {
                return $view;
            }
        }

        return null;
    }

    /**
     * Rapikan daftar centang dari form: buang nilai yang tidak dikenal,
     * dan tambahkan hak "lihat" untuk setiap hak "kelola".
     *
     * @param  string[]  $values
     * @return string[]
     */
    public static function normalize(array $values): array
    {
        $result = [];
        foreach ($values as $value) {
            $permission = self::tryFrom($value);
            if (! $permission) {
                continue;
            }
            $result[] = $permission->value;
            if ($view = $permission->viewPair()) {
                $result[] = $view->value;
            }
        }

        return array_values(array_unique($result));
    }
}
