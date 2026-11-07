<?php
/**
 * ============================================================================
 *  HIKEPASS - FUNGSI BANTUAN (TASK 02)
 * ============================================================================
 *  Berisi fungsi-fungsi kecil yang dipakai untuk memproses data sebelum
 *  dirender menjadi HTML:
 *   - e()              : escape output (mencegah XSS)
 *   - rupiah()         : format angka ke Rupiah
 *   - persen()         : hitung persentase pemakaian kuota (dibatasi 0-100)
 *   - kelasWarna()     : tentukan warna badge berdasarkan persentase
 *   - badgeStatus()    : badge status pass / jalur / tiket
 *   - kelasIkonWarna() : warna ikon program
 * ============================================================================
 */

/**
 * Escape output HTML agar aman dari injeksi script (XSS).
 * Semua data yang berasal dari PHP selalu dimasukkan ke HTML lewat fungsi ini.
 */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Format angka menjadi Rupiah, contoh: 25000 -> "Rp 25.000"
 */
function rupiah($angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

/**
 * Hitung persentase pemakaian kuota, dibatasi antara 0 sampai 100.
 */
function persen($terpakai, $kuota): float
{
    if ($kuota <= 0) {
        return 0.0;
    }
    return max(0.0, min(100.0, ($terpakai / $kuota) * 100));
}

/**
 * Tentukan kelas warna badge berdasarkan persentase pemakaian kuota.
 *  - < 60%   : hijau (aman)
 *  - 60-85%  : kuning (mulai padat)
 *  - > 85%   : merah (hampir penuh)
 */
function kelasWarna($nilaiPersen): string
{
    if ($nilaiPersen >= 85) {
        return 'bg-red-500 text-white';
    }
    if ($nilaiPersen >= 60) {
        return 'bg-amber-500 text-slate-950';
    }
    return 'bg-emerald-500 text-slate-950';
}

/**
 * Teks ringkas untuk kondisi kuota.
 */
function labelKuota($nilaiPersen): string
{
    if ($nilaiPersen >= 85) {
        return 'Hampir Penuh';
    }
    if ($nilaiPersen >= 60) {
        return 'Mulai Padat';
    }
    return 'Tersedia';
}

/**
 * Badge untuk status jalur pendakian (Open / Closed).
 */
function badgeJalur($status): string
{
    if ($status === 'Open') {
        return 'bg-emerald-500 text-slate-950';
    }
    return 'bg-red-500 text-white';
}

/**
 * Label status jalur pendakian.
 */
function labelJalur($status): string
{
    return $status === 'Open' ? 'Buka' : 'Tutup';
}

/**
 * Badge untuk status tiket (Aktif / Selesai / Dibatalkan).
 */
function badgeTiket($status): string
{
    if ($status === 'Aktif') {
        return 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30';
    }
    if ($status === 'Selesai') {
        return 'bg-slate-700 text-slate-300';
    }
    return 'bg-red-500/20 text-red-300 border border-red-500/30';
}

/**
 * Warna ikon untuk kartu program prioritas.
 */
function kelasIkonWarna($warna): string
{
    switch ($warna) {
        case 'sky':
            return 'bg-sky-500/15 text-sky-400';
        case 'amber':
            return 'bg-amber-500/15 text-amber-400';
        case 'emerald':
        default:
            return 'bg-emerald-500/15 text-emerald-400';
    }
}

/**
 * Warna garis progress bar sesuai persentase pemakaian kuota.
 */
function kelasProgress($nilaiPersen): string
{
    if ($nilaiPersen >= 85) {
        return 'bg-red-500';
    }
    if ($nilaiPersen >= 60) {
        return 'bg-amber-500';
    }
    return 'bg-emerald-500';
}
