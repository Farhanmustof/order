<?php

// Fungsi bantu format tampilan (dipakai di Blade).

if (! function_exists('rupiah')) {
    function rupiah(float|int|string|null $value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }
}

if (! function_exists('qty')) {
    /** Angka jumlah tanpa nol berlebih: 2,5 bukan 2,500. */
    function qty(float|int|string|null $value, int $decimals = 3): string
    {
        $formatted = number_format((float) $value, $decimals, ',', '.');

        return str_contains($formatted, ',') ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
    }
}

if (! function_exists('tanggal')) {
    function tanggal($date, string $format = 'd M Y'): string
    {
        return $date ? \Carbon\Carbon::parse($date)->translatedFormat($format) : '-';
    }
}
