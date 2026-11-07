<?php
/**
 * ============================================================================
 *  HIKEPASS - HALAMAN UTAMA (TASK 01, 02, 03)
 * ============================================================================
 *  BAGIAN A - PROSES DATA (TASK 02)
 *    1. Memanggil file data & fungsi bantu
 *    2. Menghitung sisa kuota, persentase, dan masa berlaku pass
 *    3. Membaca parameter filter dari URL (GET) lalu memfilter data
 *    4. Menentukan warna badge dengan pengondisian (if / elseif / else)
 *
 *  BAGIAN B - OUTPUT HTML (TASK 01 & TASK 03)
 *    1. Navbar
 *    2. Kartu Pass utama      -> $namaPendaki, $idPass, $statusPass
 *    3. Statistik ringkasan    -> $totalKuota, $kuotaTerpakai, $sisaKuota
 *    4. Timeline masa berlaku  -> loop FOR
 *    5. Program Prioritas      -> loop FOREACH $daftarProgram
 *    6. Kartu Jalur Gunung     -> loop FOREACH $daftarGunungTampil + filter
 *    7. Riwayat E-Ticket       -> loop FOREACH $daftarTiketTampil + filter
 *
 *  Prinsip: tidak ada data HikePass yang ditulis manual di dalam HTML.
 *  Seluruh isi halaman di-generate oleh PHP dari includes/data.php.
 * ============================================================================
 */

/* ===========================================================================
 * BAGIAN A - PROSES DATA (TASK 02)
 * ========================================================================== */

// --- 1. Muat sumber data & fungsi bantu ----------------------------------
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/functions.php';

// --- 2. Perhitungan dari data mentah -------------------------------------
// Sisa kuota = total kuota - kuota yang sudah terpakai (rumus sesuai Task 02)
$kuotaTerpakai = array_sum(array_column($daftarProgram, 'terpakai'));  // 35
$sisaKuota     = $totalKuota - $kuotaTerpakai;                          // 15
$persenPakai   = persen($kuotaTerpakai, $totalKuota);                  // 70%

// Masa berlaku pass
$masaBerlaku     = $periodeSelesai - $periodeMulai + 1;   // 2 tahun
$sisaTahun       = $periodeSelesai - $tahunBerjalan;       // 1 tahun
$tahunBerjalanKe = $tahunBerjalan - $periodeMulai + 1;    // 1 dari 2
$persenValid     = round(($tahunBerjalanKe / $masaBerlaku) * 100);  // 50%

// Teks periode siap tampil
$periodeTeks = $periodeMulai . ' - ' . $periodeSelesai;

// Jumlah jalur yang masih bisa dipesan (dihitung lewat loop, bukan hardcoded)
$jumlahJalurAktif = 0;
foreach ($daftarGunung as $g) {
    if ($g['status'] === 'Open' && $g['sisaKuota'] > 0) {
        $jumlahJalurAktif++;
    }
}

// Jumlah tiket yang masih aktif
$jumlahTiketAktif = 0;
foreach ($daftarTiket as $t) {
    if ($t['status'] === 'Aktif') {
        $jumlahTiketAktif++;
    }
}

// --- 3. Filter dari URL (diproses di sisi server / PHP) -------------------
$filterStatus = isset($_GET['status']) ? $_GET['status'] : 'all';
$filterCari   = isset($_GET['q']) ? trim($_GET['q']) : '';
$filterTiket  = isset($_GET['tiket']) ? $_GET['tiket'] : 'all';

$filterAktif = ($filterStatus !== 'all' || $filterCari !== '' || $filterTiket !== 'all');

// Terapkan filter ke daftar gunung
$daftarGunungTampil = [];
foreach ($daftarGunung as $g) {
    if ($filterStatus !== 'all' && $g['status'] !== $filterStatus) {
        continue;
    }
    if ($filterCari !== '') {
        $teks = strtolower($g['nama'] . ' ' . $g['jalur'] . ' ' . $g['lokasi']);
        if (strpos($teks, strtolower($filterCari)) === false) {
            continue;
        }
    }
    $daftarGunungTampil[] = $g;
}

// Terapkan filter ke daftar tiket
$daftarTiketTampil = [];
foreach ($daftarTiket as $t) {
    if ($filterTiket !== 'all' && $t['status'] !== $filterTiket) {
        continue;
    }
    if ($filterCari !== '') {
        $teks = strtolower($t['kode'] . ' ' . $t['gunung'] . ' ' . $t['jalur']);
        if (strpos($teks, strtolower($filterCari)) === false) {
            continue;
        }
    }
    $daftarTiketTampil[] = $t;
}

// --- 4. Conditional: warna badge status pass (if / elseif / else) ---------
if ($statusPass == 'Aktif' && $sisaTahun > 0) {
    $statusLabel = 'Berjalan / Aktif';
    $badgeClass  = 'bg-emerald-500 text-slate-950';
    $statusIcon  = 'fa-circle-check';
} elseif ($statusPass == 'Kadaluarsa' || $sisaTahun <= 0) {
    $statusLabel = 'Pass Kadaluarsa';
    $badgeClass  = 'bg-red-500 text-white';
    $statusIcon  = 'fa-circle-xmark';
} else {
    $statusLabel = 'Segera Habis';
    $badgeClass  = 'bg-amber-500 text-slate-950';
    $statusIcon  = 'fa-triangle-exclamation';
}

// Inisial untuk avatar (dihitung runtime dari nama pendaki)
$inisial = '';
foreach (array_slice(explode(' ', $namaPendaki), 0, 2) as $kata) {
    $inisial .= strtoupper(substr($kata, 0, 1));
}
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HikePass (Versi Array / Pertemuan 5) - Pass Pendakian <?= e($namaPendaki) ?></title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Fonts (Inter) -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet">

  <!-- Gaya utama HikePass (dipakai bersama dengan aplikasi JS) -->
  <link rel="stylesheet" href="../style.css">
  <!-- Gaya cadangan agar halaman tetap rapi wenn CDN tidak tersedia -->
  <link rel="stylesheet" href="assets/hikepass-fallback.css">
</head>
<body class="bg-slate-900 text-slate-100 font-sans antialiased min-h-screen flex flex-col">

  <!-- ================= NAVBAR ================= -->
  <header class="sticky top-0 z-40 bg-slate-900/90 backdrop-blur-md border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">

        <!-- Logo -->
        <a href="#pass" class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-slate-950 font-black text-xl shadow-lg shadow-emerald-500/20">
            <i class="fa-solid fa-mountain text-slate-900"></i>
          </div>
          <div>
            <span class="text-xl font-extrabold tracking-tight bg-gradient-to-r from-emerald-400 to-teal-200 bg-clip-text text-transparent">HikePass</span>
            <span class="hidden sm:inline-block text-xs text-slate-400 block -mt-1 font-medium">Sistem Pendakian Terpadu</span>
          </div>
        </a>

        <!-- Navigasi -->
        <nav class="hidden md:flex items-center space-x-1">
          <a href="#pass" class="nav-btn active"><i class="fa-solid fa-id-card mr-2"></i>Pass Saya</a>
          <a href="#program" class="nav-btn"><i class="fa-solid fa-layer-group mr-2"></i>Program Prioritas</a>
          <a href="#jalur" class="nav-btn"><i class="fa-solid fa-mountain mr-2"></i>Jalur Pendakian</a>
          <a href="#riwayat" class="nav-btn"><i class="fa-solid fa-clock-rotate-left mr-2"></i>Riwayat</a>
        </nav>

        <!-- Badge pemesan -->
        <div class="flex items-center space-x-3">
          <span class="px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-emerald-400 text-xs sm:text-sm font-semibold">
            <i class="fa-solid fa-user mr-1"></i> <?= e($namaPendaki) ?>
          </span>
        </div>

      </div>
    </div>
  </header>

  <!-- ================= MAIN ================= -->
  <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- ========== 1. KARTU PASS UTAMA (TASK 01) ========== -->
    <section id="pass" class="mb-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kartu identitas &amp; status pass -->
        <div class="lg:col-span-2 bg-slate-800 border border-slate-700 rounded-2xl p-6 sm:p-8 shadow-xl relative overflow-hidden">
          <!-- Aksen dekoratif -->
          <i class="fa-solid fa-mountain-sun absolute -right-6 -bottom-8 text-[180px] text-emerald-500/10 pointer-events-none"></i>

          <div class="relative z-10 flex flex-col sm:flex-row gap-6">
            <!-- Avatar -->
            <div class="shrink-0">
              <div class="w-24 h-24 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-slate-950 font-black text-3xl shadow-lg shadow-emerald-500/20">
                <?= e($inisial) ?>
              </div>
            </div>

            <!-- Data pemegang pass -->
            <div class="flex-1 min-w-0">
              <div class="flex flex-wrap items-center gap-2 mb-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide <?= e($badgeClass) ?>">
                  <i class="fa-solid <?= e($statusIcon) ?> mr-1"></i> <?= e($statusLabel) ?>
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-700 text-slate-200">
                  <i class="fa-solid fa-compass mr-1 text-amber-400"></i> <?= e($kategoriPass) ?>
                </span>
              </div>

              <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-1">
                <?= e($namaPendaki) ?>
              </h1>
              <p class="text-sm text-slate-400 mb-5">
                ID Pass:
                <span class="font-mono text-emerald-400 font-bold"><?= e($idPass) ?></span>
                <span class="text-slate-500">/</span>
                Periode: <span class="font-semibold text-slate-300"><?= e($periodeTeks) ?></span>
              </p>

              <!-- Baris informasi -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div>
                  <div class="text-xs text-slate-500 uppercase tracking-wide mb-1">Masa Berlaku Pass</div>
                  <div class="font-bold text-white text-lg">
                    <i class="fa-regular fa-calendar-check text-emerald-400 mr-1"></i><?= e($periodeTeks) ?>
                  </div>
                </div>
                <div>
                  <div class="text-xs text-slate-500 uppercase tracking-wide mb-1">Status Pass</div>
                  <div class="font-bold text-white text-lg">
                    <i class="fa-solid fa-shield-halved text-emerald-400 mr-1"></i><?= e($statusPass) ?>
                  </div>
                </div>
                <div>
                  <div class="text-xs text-slate-500 uppercase tracking-wide mb-1">Sisa Masa Aktif</div>
                  <div class="font-bold text-white text-lg">
                    <i class="fa-regular fa-hourglass-half text-emerald-400 mr-1"></i><?= (int) $sisaTahun ?> Tahun
                  </div>
                </div>
              </div>

              <!-- Progress masa berlaku -->
              <div class="mt-6">
                <div class="flex justify-between text-xs text-slate-400 mb-1.5">
                  <span>Berlaku <?= (int) $tahunBerjalanKe ?> dari <?= (int) $masaBerlaku ?> tahun</span>
                  <span class="font-bold text-emerald-400"><?= (int) $persenValid ?>%</span>
                </div>
                <div class="h-2 w-full rounded-full bg-slate-700 overflow-hidden">
                  <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-400" style="width: <?= (int) $persenValid ?>%"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Detail akun -->
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 shadow-xl space-y-4">
          <h2 class="text-sm font-bold text-white uppercase tracking-wide flex items-center gap-2">
            <i class="fa-solid fa-address-card text-emerald-400"></i> Data Pemegang
          </h2>
          <div class="space-y-3 text-sm">
            <div class="flex justify-between gap-3">
              <span class="text-slate-400">ID Pass</span>
              <span class="font-mono font-bold text-emerald-400"><?= e($idPass) ?></span>
            </div>
            <div class="flex justify-between gap-3">
              <span class="text-slate-400">Kategori</span>
              <span class="font-semibold text-slate-200"><?= e($kategoriPass) ?></span>
            </div>
            <div class="flex justify-between gap-3">
              <span class="text-slate-400">Periode</span>
              <span class="font-semibold text-emerald-400"><?= e($periodeTeks) ?></span>
            </div>
            <div class="flex justify-between gap-3">
              <span class="text-slate-400">Status</span>
              <span class="font-semibold text-emerald-400"><?= e($statusPass) ?></span>
            </div>
            <div class="flex justify-between gap-3">
              <span class="text-slate-400">NIK</span>
              <span class="font-mono text-slate-200"><?= e($nikPendaki) ?></span>
            </div>
            <div class="flex justify-between gap-3">
              <span class="text-slate-400">Email</span>
              <span class="text-slate-200 text-right"><?= e($emailPendaki) ?></span>
            </div>
          </div>
          <div class="pt-4 border-t border-slate-700/60">
            <div class="text-xs text-slate-500 mb-2">Jalur Prioritas Terpilih</div>
            <div class="flex flex-wrap gap-2">
              <?php foreach ($daftarProgram as $p): ?>
                <span class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-700 text-xs font-semibold text-slate-300">
                  <?= e($p['nama']) ?> / <?= e($p['jalur']) ?>
                </span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ========== 2. STATISTIK KUOTA (TASK 02 - hasil perhitungan) ========== -->
    <section class="mb-8">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-slate-800 p-5 rounded-xl border border-slate-700">
          <div class="text-slate-400 text-xs font-medium uppercase mb-1">Total Kuota</div>
          <div class="text-3xl font-extrabold text-white"><?= (int) $totalKuota ?></div>
          <div class="text-xs text-slate-500 mt-1">Slot pass HikePass</div>
        </div>

        <div class="bg-slate-800 p-5 rounded-xl border border-slate-700">
          <div class="text-slate-400 text-xs font-medium uppercase mb-1">Kuota Terpakai</div>
          <div class="text-3xl font-extrabold text-amber-400"><?= (int) $kuotaTerpakai ?></div>
          <div class="text-xs text-slate-500 mt-1"><?= (int) $persenPakai ?>% dari total kuota</div>
        </div>

        <div class="bg-slate-800 p-5 rounded-xl border border-slate-700">
          <div class="text-slate-400 text-xs font-medium uppercase mb-1">Sisa Kuota</div>
          <div class="text-3xl font-extrabold text-emerald-400"><?= (int) $sisaKuota ?></div>
          <div class="text-xs text-slate-500 mt-1"><?= (int) $jumlahJalurAktif ?> jalur masih dibuka</div>
        </div>

        <div class="bg-slate-800 p-5 rounded-xl border border-slate-700">
          <div class="text-slate-400 text-xs font-medium uppercase mb-1">Tiket Aktif</div>
          <div class="text-3xl font-extrabold text-sky-400"><?= (int) $jumlahTiketAktif ?></div>
          <div class="text-xs text-slate-500 mt-1">dari <?= count($daftarTiket) ?> riwayat tiket</div>
        </div>
      </div>
    </section>

    <!-- ========== 3. TIMELINE MASA BERLAKU (TASK 02/03 - loop FOR) ========== -->
    <section class="mb-8 bg-slate-800 border border-slate-700 rounded-2xl p-6 sm:p-8 shadow-xl">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
          <i class="fa-solid fa-timeline text-emerald-400"></i> Timeline Masa Berlaku Pass
        </h2>
        <span class="text-xs text-slate-400">Periode <?= e($periodeTeks) ?></span>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Loop FOR: setiap tahun dalam masa berlaku pass -->
        <div class="grid grid-cols-2 gap-3 lg:col-span-2">
          <?php
          for ($tahun = $periodeMulai; $tahun <= $periodeSelesai; $tahun++) {
              // Conditional penentu status setiap tahun
              if ($tahun < $tahunBerjalan) {
                  $labelTahun = 'Berlalu';
                  $warnaTahun = 'bg-slate-700 text-slate-400 border-slate-700';
                  $ikonTahun  = 'fa-circle-check';
              } elseif ($tahun === $tahunBerjalan) {
                  $labelTahun = 'Aktif';
                  $warnaTahun = 'bg-emerald-500/15 text-emerald-300 border-emerald-500/40';
                  $ikonTahun  = 'fa-circle-dot';
              } else {
                  $labelTahun = 'Mendatang';
                  $warnaTahun = 'bg-slate-800 text-slate-500 border-slate-700';
                  $ikonTahun  = 'fa-circle';
              }
          ?>
            <div class="rounded-xl border <?= e($warnaTahun) ?> <?= $tahun === $tahunBerjalan ? 'ring-2 ring-emerald-500/40' : '' ?> px-4 py-3 text-center">
              <div class="text-lg font-extrabold text-white"><?= (int) $tahun ?></div>
              <div class="text-[11px] font-semibold mt-1 flex items-center justify-center gap-1">
                <i class="fa-solid <?= e($ikonTahun) ?>"></i><?= e($labelTahun) ?>
              </div>
            </div>
          <?php
          }
          ?>
        </div>

        <!-- Ringkasan masa berlaku -->
        <div class="rounded-xl bg-slate-900/60 border border-slate-700 p-5 flex flex-col justify-center">
          <div class="text-xs text-slate-500 uppercase tracking-wide mb-1">Masa Berlaku Terpakai</div>
          <div class="text-3xl font-extrabold text-white mb-3"><?= (int) $persenValid ?>%</div>
          <div class="h-2 w-full rounded-full bg-slate-700 overflow-hidden">
            <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-400" style="width: <?= (int) $persenValid ?>%"></div>
          </div>
          <div class="text-[11px] text-slate-500 mt-2">
            <?= (int) $tahunBerjalanKe ?> dari <?= (int) $masaBerlaku ?> tahun &#183; sisa <?= (int) $sisaTahun ?> tahun
          </div>
        </div>
      </div>
    </section>

    <!-- ========== 4. PROGRAM PRIORITAS (TASK 03 - loop FOREACH) ========== -->
    <section id="program" class="mb-8">
      <div class="mb-6">
        <h2 class="text-2xl font-bold text-white flex items-center gap-2">
          <i class="fa-solid fa-layer-group text-emerald-400"></i> Program / Jalur Prioritas
        </h2>
        <p class="text-slate-400 text-sm mt-1">
          Alokasi kuota setiap program di-generate otomatis oleh PHP dari array
          <code class="text-emerald-400">$daftarProgram</code> (total kuota <?= (int) $totalKuota ?> slot).
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($daftarProgram as $p): ?>
            <?php
            $pakaiPersen = persen($p['terpakai'], $p['kuota']);
            $sisaSlot    = $p['kuota'] - $p['terpakai'];
            ?>
            <div class="bg-slate-800 rounded-2xl border border-slate-700 shadow-lg flex flex-col hover:border-emerald-500/50 transition duration-300">
              <div class="p-5 flex-1 flex flex-col">
                <!-- Header kartu -->
                <div class="flex items-start justify-between mb-4">
                  <div class="w-12 h-12 rounded-xl <?= e(kelasIkonWarna($p['warna'])) ?> flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid <?= e($p['ikon']) ?>"></i>
                  </div>
                  <div class="text-right">
                    <div class="text-[10px] uppercase tracking-widest text-slate-500">Prioritas</div>
                    <div class="text-2xl font-black text-white leading-none"><?= (int) $p['prioritas'] ?></div>
                  </div>
                </div>

                <h3 class="text-lg font-bold text-white mb-1"><?= e($p['nama']) ?></h3>
                <div class="text-xs font-semibold text-emerald-400 mb-2"><?= e($p['jalur']) ?></div>
                <p class="text-xs text-slate-500 mb-1"><?= e($p['kategori']) ?></p>
                <p class="text-slate-400 text-xs line-clamp-2 mb-4"><?= e($p['deskripsi']) ?></p>

                <!-- Badge status -->
                <div class="mb-4">
                  <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?= e(kelasWarna($pakaiPersen)) ?>">
                    <?= e(labelKuota($pakaiPersen)) ?>
                  </span>
                </div>
              </div>

              <!-- Kuota -->
              <div class="border-t border-slate-700/60 pt-4 px-5 pb-5">
                <div class="flex justify-between items-center mb-2 text-xs">
                  <span class="text-slate-400">Kuota terpakai:</span>
                  <span class="font-bold text-slate-200"><?= (int) $p['terpakai'] ?> / <?= (int) $p['kuota'] ?> slot</span>
                </div>
                <div class="h-1.5 w-full rounded-full bg-slate-700 overflow-hidden mb-3">
                  <div class="h-full rounded-full <?= e(kelasProgress($pakaiPersen)) ?>" style="width: <?= (int) $pakaiPersen ?>%"></div>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-[10px] text-slate-400">Sisa slot</span>
                  <span class="text-sm font-extrabold text-white"><?= (int) $sisaSlot ?> slot</span>
                </div>
              </div>
            </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- ========== 5. JALUR / GUNUNG (TASK 03 - loop FOREACH + filter) ========== -->
    <section id="jalur" class="mb-8">
      <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
        <div>
          <h2 class="text-2xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-mountain text-emerald-400"></i> Jalur &amp; Gunung Pendakian
          </h2>
          <p class="text-slate-400 text-sm mt-1">
            Kartu di-generate oleh PHP dari array <code class="text-emerald-400">$daftarGunung</code>
            (<?= count($daftarGunungTampil) ?> dari <?= count($daftarGunung) ?> data ditampilkan).
          </p>
        </div>

        <!-- Form filter diproses PHP (method GET) -->
        <form method="get" action="index-array.php" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
          <input type="hidden" name="tiket" value="<?= e($filterTiket) ?>">
          <div class="w-full sm:w-56">
            <input type="text" name="q" value="<?= e($filterCari) ?>" placeholder="Cari gunung, jalur, lokasi..." class="input-field">
          </div>
          <select name="status" class="input-field w-auto" onchange="this.form.submit()">
            <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>Semua Status</option>
            <option value="Open" <?= $filterStatus === 'Open' ? 'selected' : '' ?>>Buka (Open)</option>
            <option value="Closed" <?= $filterStatus === 'Closed' ? 'selected' : '' ?>>Tutup (Closed)</option>
          </select>
          <button type="submit" class="btn-primary shrink-0">
            <i class="fa-solid fa-magnifying-glass mr-2"></i> Terapkan
          </button>
        </form>
      </div>

      <?php if ($filterAktif): ?>
        <div class="mb-4 text-sm">
          <a href="index-array.php" class="btn-secondary px-3 py-2 text-xs">
            <i class="fa-solid fa-rotate-left mr-1"></i> Reset filter
          </a>
        </div>
      <?php endif; ?>

      <?php if (count($daftarGunungTampil) === 0): ?>
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-12 text-center text-slate-400">
          <i class="fa-solid fa-mountain-sun text-3xl mb-3 block"></i>
          Tidak ada jalur yang cocok dengan filter.
        </div>
      <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach ($daftarGunungTampil as $g): ?>
              <?php
              // Conditional status jalur: Closed atau kuota habis -> tombol nonaktif
              $isClosed = ($g['status'] === 'Closed' || $g['sisaKuota'] <= 0);
              $kuotaTerpakaiJalur = $g['kuotaMaks'] - $g['sisaKuota'];
              $kuotaPersen = persen($kuotaTerpakaiJalur, $g['kuotaMaks']);
              ?>
              <div class="bg-slate-800 rounded-2xl overflow-hidden border border-slate-700 shadow-lg flex flex-col hover:border-emerald-500/50 transition duration-300">
                <!-- Gambar + badge status -->
                <div class="relative h-48 overflow-hidden">
                  <img src="<?= e($g['gambar']) ?>" alt="<?= e($g['nama']) ?>" class="w-full h-full object-cover">
                  <span class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-bold <?= e(badgeJalur($g['status'])) ?> shadow">
                    <?= e(labelJalur($g['status'])) ?>
                  </span>
                  <div class="absolute bottom-3 left-3 bg-slate-900/80 backdrop-blur-sm px-2.5 py-1 rounded-md text-xs text-slate-200 font-semibold">
                    <i class="fa-solid fa-mountain text-emerald-400 mr-1"></i> <?= e($g['ketinggian']) ?>
                  </div>
                </div>

                <div class="p-5 flex-1 flex flex-col justify-between">
                  <div>
                    <div class="text-xs text-emerald-400 font-semibold mb-1">
                      <i class="fa-solid fa-location-dot mr-1"></i><?= e($g['lokasi']) ?>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2"><?= e($g['nama']) ?> - Jalur <?= e($g['jalur']) ?></h3>
                    <p class="text-slate-400 text-xs line-clamp-2 mb-4"><?= e($g['deskripsi']) ?></p>

                    <!-- Persyaratan -->
                    <ul class="space-y-1 mb-4">
                      <?php foreach ($g['persyaratan'] as $syarat): ?>
                        <li class="text-[11px] text-slate-400 flex items-start gap-1.5">
                          <i class="fa-solid fa-circle-check text-emerald-500 mt-[2px]"></i><?= e($syarat) ?>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  </div>

                  <div class="border-t border-slate-700/60 pt-4 mt-2">
                    <div class="flex justify-between items-center mb-3 text-xs">
                      <span class="text-slate-400">Sisa Kuota Hari Ini:</span>
                      <span class="font-bold <?= $g['sisaKuota'] > 0 ? 'text-emerald-400' : 'text-red-400' ?>">
                        <?= (int) $g['sisaKuota'] ?> / <?= (int) $g['kuotaMaks'] ?> Slot
                      </span>
                    </div>
                    <div class="h-1.5 w-full rounded-full bg-slate-700 overflow-hidden mb-4">
                      <div class="h-full rounded-full <?= e(kelasProgress($kuotaPersen)) ?>" style="width: <?= (int) $kuotaPersen ?>%"></div>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                      <div>
                        <span class="text-[10px] text-slate-400 block">Tiket Masuk:</span>
                        <span class="text-base font-extrabold text-white"><?= e(rupiah($g['harga'])) ?></span>
                      </div>
                      <button <?= $isClosed ? 'disabled class="btn-secondary px-3 py-2 text-xs opacity-50 cursor-not-allowed"' : 'class="btn-primary px-3 py-2 text-xs"' ?>>
                        Pesan Tiket
                      </button>
                    </div>
                  </div>
                </div>
              </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <!-- ========== 6. RIWAYAT E-TICKET (TASK 03 - loop FOREACH + filter) ========== -->
    <section id="riwayat" class="mb-8">
      <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
        <div>
          <h2 class="text-2xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-emerald-400"></i> Riwayat E-Ticket
          </h2>
          <p class="text-slate-400 text-sm mt-1">
            Riwayat tiket <?= e($namaPendaki) ?> di-generate oleh PHP dari array
            <code class="text-emerald-400">$daftarTiket</code>
            (<?= count($daftarTiketTampil) ?> dari <?= count($daftarTiket) ?> data ditampilkan).
          </p>
        </div>

        <!-- Filter status tiket (diproses PHP) -->
        <form method="get" action="index-array.php" class="w-full sm:w-auto">
          <input type="hidden" name="status" value="<?= e($filterStatus) ?>">
          <input type="hidden" name="q" value="<?= e($filterCari) ?>">
          <select name="tiket" class="input-field w-auto" onchange="this.form.submit()">
            <option value="all" <?= $filterTiket === 'all' ? 'selected' : '' ?>>Semua Status Tiket</option>
            <option value="Aktif" <?= $filterTiket === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="Selesai" <?= $filterTiket === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
          </select>
        </form>
      </div>

      <?php if (count($daftarTiketTampil) === 0): ?>
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-12 text-center text-slate-400">
          <i class="fa-solid fa-ticket text-3xl mb-3 block"></i>
          Tidak ada tiket yang cocok dengan filter.
        </div>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($daftarTiketTampil as $t): ?>
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center gap-4 justify-between hover:border-emerald-500/40 transition">
              <div class="flex items-center gap-4">
                <div class="w-11 h-11 rounded-lg bg-slate-900 border border-slate-700 flex items-center justify-center text-emerald-400 shrink-0">
                  <i class="fa-solid fa-ticket"></i>
                </div>
                <div>
                  <div class="font-mono font-bold text-white text-sm"><?= e($t['kode']) ?></div>
                  <div class="text-xs text-slate-400 mt-0.5">
                    <?= e($t['gunung']) ?> - Jalur <?= e($t['jalur']) ?> &#8226; <?= e($t['tanggal']) ?>
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-3 sm:justify-end">
                <span class="text-sm font-bold text-slate-200"><?= e(rupiah($t['harga'])) ?></span>
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?= e(badgeTiket($t['status'])) ?>">
                  <?= e($t['status']) ?>
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

  </main>

  <!-- ================= FOOTER ================= -->
  <footer class="bg-slate-950 border-t border-slate-800 py-6 mt-12 text-center text-xs text-slate-500">
    <div class="max-w-7xl mx-auto px-4">
      <p>&#169; <?= (int) date('Y') ?> HikePass Inc. Platform Pemesanan Tiket Pendakian Terorganisir.</p>
      <p class="mt-1">Halaman ini di-render oleh PHP &#8212; data diproses di server, HTML di-generate otomatis.</p>
    </div>
  </footer>

</body>
</html>
