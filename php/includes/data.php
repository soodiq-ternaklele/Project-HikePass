<?php
/**
 * ============================================================================
 *  HIKEPASS - SUMBER DATA
 * ============================================================================
 *  File ini berisi seluruh data HikePass dalam bentuk ARRAY PHP.
 *
 *  Dua cara pemakaian:
 *   1. CLASS HikePass  -> $pass = new HikePass(require __DIR__ . '/data.php');
 *                         (file ini mengembalikan array dataset)
 *   2. VERSI ARRAY    -> require_once __DIR__ . '/data.php';
 *                         (variabel di bawah otomatis tersedia)
 *
 *  Prinsip: data hanya ada di satu tempat, sehingga mudah diperbarui.
 * ============================================================================
 */

/* ===========================================================================
 * VARIABEL DATA PEMEGANG PASS & TOTAL KUOTA
 * ========================================================================= */
$namaPendaki    = 'Aris Setiawan';
$idPass         = 'HKP-2026-0091';
$kategoriPass   = 'Pendaki Jelajah';
$statusPass     = 'Aktif';
$emailPendaki   = 'aris.setiawan@student.ac.id';
$nikPendaki     = '3507120903030091';
$periodeMulai   = 2026;          // Tahun mulai berlaku pass
$periodeSelesai = 2027;          // Tahun akhir berlaku pass

/*
 * Tahun berjalan pada skenario pass HikePass (2026 - 2027).
 * Dipakai untuk menghitung sisa masa aktif dan status setiap tahun.
 */
$tahunBerjalan = 2026;

/* Total kuota slot HikePass */
$totalKuota = 50;

/* ===========================================================================
 * ARRAY: PROGRAM PRIORITAS & ALOKASI KUOTA
 *  Total kuota seluruh program = 20 + 12 + 18 = 50 (sama dengan $totalKuota)
 * ========================================================================= */
$daftarProgram = [
    [
        'nama'      => 'Pendidikan',
        'jalur'     => 'Jalur 1',
        'prioritas' => 1,
        'kategori'  => 'Beasiswa & Subsidisasi Pendidikan',
        'kuota'     => 20,
        'terpakai'  => 11,
        'status'    => 'Open',
        'ikon'      => 'fa-graduation-cap',
        'warna'     => 'emerald',
        'deskripsi' => 'Jalur prioritas bagi pendaki berstatus pelajar/mahasiswa dengan bukti pendaftaran pendidikan aktif.',
    ],
    [
        'nama'      => 'Kesehatan',
        'jalur'     => 'Jalur 2',
        'prioritas' => 2,
        'kategori'  => 'Pemeriksaan & Sertifikasi Kesehatan',
        'kuota'     => 12,
        'terpakai'  => 8,
        'status'    => 'Open',
        'ikon'      => 'fa-heart-pulse',
        'warna'     => 'sky',
        'deskripsi' => 'Kuota khusus untuk pendaki dengan riwayat medical terverifikasi dan surat keterangan sehat.',
    ],
    [
        'nama'      => 'Infrastruktur',
        'jalur'     => 'Jalur 3',
        'prioritas' => 3,
        'kategori'  => 'Pembangunan & Operasional Infrastruktur',
        'kuota'     => 18,
        'terpakai'  => 16,
        'status'    => 'Open',
        'ikon'      => 'fa-road',
        'warna'     => 'amber',
        'deskripsi' => 'Kuota untuk tim pembangunan, pemeliharaan jalur, dan dukungan logistik pos pendakian.',
    ],
];

/* ===========================================================================
 * ARRAY: JALUR / GUNUNG PENDAKIAN (dirender sebagai kartu)
 * ========================================================================= */
$daftarGunung = [
    [
        'id'          => 'G1',
        'nama'        => 'Gunung Semeru',
        'jalur'       => 'Ranu Pane',
        'lokasi'      => 'Lumajang, Jawa Timur',
        'ketinggian'  => '3.676 MDPL',
        'harga'       => 25000,
        'kuotaMaks'   => 100,
        'sisaKuota'   => 45,
        'status'      => 'Open',
        'gambar'      => 'https://images.unsplash.com/photo-1589553460730-57f97022d4f2?auto=format&fit=crop&w=600&q=80',
        'deskripsi'   => 'Atap pulau Jawa dengan keindahan Danau Ranu Kumbolo dan Puncak Mahameru.',
        'persyaratan' => ['Surat Keterangan Sehat', 'Fotokopi KTP', 'Peralatan Standar Pendakian'],
    ],
    [
        'id'          => 'G2',
        'nama'        => 'Gunung Rinjani',
        'jalur'       => 'Sembalun',
        'lokasi'      => 'Lombok, NTB',
        'ketinggian'  => '3.726 MDPL',
        'harga'       => 50000,
        'kuotaMaks'   => 80,
        'sisaKuota'   => 12,
        'status'      => 'Open',
        'gambar'      => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=600&q=80',
        'deskripsi'   => 'Dikenal dengan keindahan Danau Segara Anak dan sabana Sembalun yang membentang.',
        'persyaratan' => ['Surat Keterangan Sehat', 'Kartu Identitas Diri', 'Deposit Bebas Sampah'],
    ],
    [
        'id'          => 'G3',
        'nama'        => 'Gunung Gede',
        'jalur'       => 'Cibodas',
        'lokasi'      => 'Cianjur, Jawa Barat',
        'ketinggian'  => '2.958 MDPL',
        'harga'       => 30000,
        'kuotaMaks'   => 150,
        'sisaKuota'   => 0,
        'status'      => 'Closed',
        'gambar'      => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=600&q=80',
        'deskripsi'   => 'Jalur favorit pendaki dengan pemandangan Telaga Warna dan Alun-alun Suryakencana.',
        'persyaratan' => ['Surat Keterangan Sehat', 'KTP/SIM/Kartu Pelajar'],
    ],
    [
        'id'          => 'G4',
        'nama'        => 'Gunung Prau',
        'jalur'       => 'Patak Banteng',
        'lokasi'      => 'Wonosobo, Jawa Tengah',
        'ketinggian'  => '2.565 MDPL',
        'harga'       => 20000,
        'kuotaMaks'   => 120,
        'sisaKuota'   => 65,
        'status'      => 'Open',
        'gambar'      => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=600&q=80',
        'deskripsi'   => 'Pemandangan golden sunrise terbaik se-Asia Tenggara dengan bukit teletubbies.',
        'persyaratan' => ['Surat Keterangan Sehat', 'Fotokopi KTP'],
    ],
];

/* ===========================================================================
 * ARRAY: RIWAYAT E-TICKET
 * ========================================================================= */
$daftarTiket = [
    [
        'kode'    => 'HKP-2290',
        'gunung'  => 'Gunung Semeru',
        'jalur'   => 'Ranu Pane',
        'tanggal' => '14 Nov 2026',
        'status'  => 'Selesai',
        'harga'   => 25000,
    ],
    [
        'kode'    => 'HKP-3184',
        'gunung'  => 'Gunung Prau',
        'jalur'   => 'Patak Banteng',
        'tanggal' => '05 Des 2026',
        'status'  => 'Aktif',
        'harga'   => 20000,
    ],
    [
        'kode'    => 'HKP-5507',
        'gunung'  => 'Gunung Rinjani',
        'jalur'   => 'Sembalun',
        'tanggal' => '18 Jan 2027',
        'status'  => 'Aktif',
        'harga'   => 50000,
    ],
];

/* ===========================================================================
 * DATASET YANG DIKEMBALIKAN KE CLASS HikePass
 * ======================================================================= */
return [
    'nama'           => $namaPendaki,
    'idPass'         => $idPass,
    'kategori'       => $kategoriPass,
    'status'         => $statusPass,
    'email'          => $emailPendaki,
    'nik'            => $nikPendaki,
    'periodeMulai'   => $periodeMulai,
    'periodeSelesai' => $periodeSelesai,
    'tahunBerjalan'  => $tahunBerjalan,
    'totalKuota'     => $totalKuota,
    'programs'       => $daftarProgram,
    'jalur'          => $daftarGunung,
    'tiket'          => $daftarTiket,
];
