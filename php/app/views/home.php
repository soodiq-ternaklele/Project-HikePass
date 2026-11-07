<?php
/**
 * ============================================================================
 *  HIKEPASS - VIEW HOME (PERTEMUAN 7 - MVC)
 * ============================================================================
 *  View tidak menyimpan logika bisnis: seluruh data dikirim oleh Controller
 *  (php/index.php -> HikePassController -> HikePass -> view ini).
 *
 *  PENJAGA AKSES LANGSUNG:
 *  Bila file ini dibuka langsung di browser (mis. .../php/app/views/home.php
 *  tanpa melalui Controller), blok di bawah otomatis memuat helper e() dan
 *  data dari Model sehingga halaman tetap ter-render utuh — bukan
 *  "Fatal error: Call to undefined function e()".
 *  Jalur utama aplikasi tetap: index.php -> Controller -> Model -> View ini.
 * ============================================================================
 */

// Deteksi akses langsung: URL mengandung /app/views (lewat Controller tidak).
$__direct = str_contains(
    str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')),
    '/app/views'
);

/*
 * Default aman (top-level) ---
 * Saat direndah lewat Controller, extract() SUDAH mengisi variabel ini,
 * sehingga `?? []` tidak menimpa data asli. Saat diakses langsung, variabel
 * tetap terdefinisi supaya analisa statis (Intelephense) tidak menandai P1116.
 */
$profile      = $profile ?? [];
$statusInfo   = $statusInfo ?? [];
$programs     = $programs ?? [];
$statistik    = $statistik ?? [];
$timeline     = $timeline ?? [];
$jalurTampil  = $jalurTampil ?? [];
$tiketTampil  = $tiketTampil ?? [];
$totalKuota   = (int) ($totalKuota ?? 0);
$totalJalur   = (int) ($totalJalur ?? 0);
$totalTiket   = (int) ($totalTiket ?? 0);
$filterStatus = (string) ($filterStatus ?? 'all');
$filterCari   = (string) ($filterCari ?? '');
$filterTiket  = (string) ($filterTiket ?? 'all');
$filterAktif  = (bool) ($filterAktif ?? false);

/*
 * Akses langsung tanpa Controller: muat helper + isi data dari Model,
 * persis seperti yang dilakukan Controller (php/index.php).
 */
if ($__direct && !function_exists('e')) {
    $__phpRoot = dirname(__DIR__, 2); // app/views -> app -> php

    require_once $__phpRoot . '/includes/functions.php';      // e(), rupiah(), dll.
    require_once $__phpRoot . '/app/models/HikePass.php';
    $dataset = require $__phpRoot . '/includes/data.php';
    $pass    = new HikePass($dataset);

    // Filter default 'all' karena tidak ada query $_GET dari form.
    $filterStatus = 'all';
    $filterCari   = '';
    $filterTiket  = 'all';
    $filterAktif  = false;

    $profile      = $pass->getProfile();
    $statusInfo   = $pass->getStatusInfo();
    $programs     = $pass->getPrograms();
    $statistik    = $pass->getStatistik();
    $timeline     = $pass->getTimeline();
    $jalurTampil  = $pass->filterJalur($filterStatus, $filterCari);
    $tiketTampil  = $pass->filterTiket($filterTiket, $filterCari);
    $totalKuota   = $pass->getTotalKuota();
    $totalJalur   = count($pass->getJalur());
    $totalTiket   = count($pass->getTiket());
}
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HikePass - Pass Pendakian <?= e($profile['nama']) ?></title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Google Fonts (Inter) -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet">

  <!-- Gaya utama HikePass (dipakai bersama dengan aplikasi JS) -->
  <link rel="stylesheet" href="<?= $__direct ? '../../../style.css' : '../style.css' ?>">
  <!-- Gaya cadangan agar halaman tetap rapi bila CDN tidak tersedia -->
  <link rel="stylesheet" href="<?= $__direct ? '../../../php/assets/hikepass-fallback.css' : 'assets/hikepass-fallback.css' ?>">
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

        <!-- Badge pemesan (dari object) -->
        <div class="flex items-center space-x-3">
          <span class="px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-emerald-400 text-xs sm:text-sm font-semibold">
            <i class="fa-solid fa-user mr-1"></i> <?= e($profile['nama']) ?>
          </span>
        </div>

      </div>
    </div>
  </header>

  <!-- ================= NAVIGASI MOBILE (RESPONSIVE) ================= -->
  <nav class="md:hidden bg-slate-900/95 border-b border-slate-800" aria-label="Navigasi mobile">
    <div class="max-w-7xl mx-auto px-2 sm:px-6 py-2 grid grid-cols-4 gap-1">
      <a href="#pass" class="nav-btn active text-center whitespace-nowrap"><i class="fa-solid fa-id-card mr-1"></i> Pass</a>
      <a href="#program" class="nav-btn text-center whitespace-nowrap"><i class="fa-solid fa-layer-group mr-1"></i> Program</a>
      <a href="#jalur" class="nav-btn text-center whitespace-nowrap"><i class="fa-solid fa-mountain mr-1"></i> Jalur</a>
      <a href="#riwayat" class="nav-btn text-center whitespace-nowrap"><i class="fa-solid fa-clock-rotate-left mr-1"></i> Tiket</a>
    </div>
  </nav>

  <!-- ================= MAIN ================= -->
  <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- ========== 1. KARTU PASS UTAMA (TASK 01 - data dari object) ========== -->
    <section id="pass" class="mb-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kartu identitas & status pass -->
        <div class="lg:col-span-2 bg-slate-800 border border-slate-700 rounded-2xl p-6 sm:p-8 shadow-xl relative overflow-hidden">
          <!-- Aksen dekoratif -->
          <i class="fa-solid fa-mountain-sun absolute -right-6 -bottom-8 text-[180px] text-emerald-500/10 pointer-events-none"></i>

          <div class="relative z-10 flex flex-col sm:flex-row gap-6">
            <!-- Avatar -->
            <div class="shrink-0">
              <div class="w-24 h-24 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-slate-950 font-black text-3xl shadow-lg shadow-emerald-500/20">
                <?= e($profile['inisial']) ?>
              </div>
            </div>

            <!-- Data pemegang pass -->
            <div class="flex-1 min-w-0">
              <div class="flex flex-wrap items-center gap-2 mb-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide <?= e($statusInfo['kelas']) ?>">
                  <i class="fa-solid <?= e($statusInfo['ikon']) ?> mr-1"></i> <?= e($statusInfo['label']) ?>
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-700 text-slate-200">
                  <i class="fa-solid fa-compass mr-1 text-amber-400"></i> <?= e($profile['kategori']) ?>
                </span>
              </div>

              <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-1">
                <?= e($profile['nama']) ?>
              </h1>
              <p class="text-sm text-slate-400 mb-5">
                ID Pass:
                <span class="font-mono text-emerald-400 font-bold"><?= e($profile['idPass']) ?></span>
                <span class="text-slate-500">/</span>
                Periode: <span class="font-semibold text-slate-300"><?= e($profile['periode']) ?></span>
              </p>

              <!-- Baris informasi -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div>
                  <div class="text-xs text-slate-500 uppercase tracking-wide mb-1">Masa Berlaku Pass</div>
                  <div class="font-bold text-white text-lg">
                    <i class="fa-regular fa-calendar-check text-emerald-400 mr-1"></i><?= e($profile['periode']) ?>
                  </div>
                </div>
                <div>
                  <div class="text-xs text-slate-500 uppercase tracking-wide mb-1">Status Pass</div>
                  <div class="font-bold text-white text-lg">
                    <i class="fa-solid fa-shield-halved text-emerald-400 mr-1"></i><?= e($profile['status']) ?>
                  </div>
                </div>
                <div>
                  <div class="text-xs text-slate-500 uppercase tracking-wide mb-1">Sisa Masa Aktif</div>
                  <div class="font-bold text-white text-lg">
                    <i class="fa-regular fa-hourglass-half text-emerald-400 mr-1"></i><?= (int) $profile['sisaTahun'] ?> Tahun
                  </div>
                </div>
              </div>

              <!-- Progress masa berlaku -->
              <div class="mt-6">
                <div class="flex justify-between text-xs text-slate-400 mb-1.5">
                  <span>Berlaku <?= (int) $profile['persenValid'] ?>% dari <?= (int) $profile['masaBerlaku'] ?> tahun</span>
                  <span class="font-bold text-emerald-400"><?= (int) $profile['persenValid'] ?>%</span>
                </div>
                <div class="h-2 w-full rounded-full bg-slate-700 overflow-hidden">
                  <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-400" style="width: <?= (int) $profile['persenValid'] ?>%"></div>
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
              <span class="font-mono font-bold text-emerald-400"><?= e($profile['idPass']) ?></span>
            </div>
            <div class="flex justify-between gap-3">
              <span class="text-slate-400">Kategori</span>
              <span class="font-semibold text-slate-200"><?= e($profile['kategori']) ?></span>
            </div>
            <div class="flex justify-between gap-3">
              <span class="text-slate-400">Periode</span>
              <span class="font-semibold text-emerald-400"><?= e($profile['periode']) ?></span>
            </div>
            <div class="flex justify-between gap-3">
              <span class="text-slate-400">Status</span>
              <span class="font-semibold text-emerald-400"><?= e($profile['status']) ?></span>
            </div>
            <div class="flex justify-between gap-3">
              <span class="text-slate-400">NIK</span>
              <span class="font-mono text-slate-200"><?= e($profile['nik']) ?></span>
            </div>
            <div class="flex justify-between gap-3">
              <span class="text-slate-400">Email</span>
              <span class="text-slate-200 text-right"><?= e($profile['email']) ?></span>
            </div>
          </div>
          <div class="pt-4 border-t border-slate-700/60">
            <div class="text-xs text-slate-500 mb-2">Jalur Prioritas Terpilih</div>
            <div class="flex flex-wrap gap-2">
              <?php foreach ($programs as $p): ?>
                <span class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-700 text-xs font-semibold text-slate-300">
                  <?= e($p['nama']) ?> / <?= e($p['jalur']) ?>
                </span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ========== 2. STATISTIK KUOTA (dari method getStatistik) ========== -->
    <section class="mb-8">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php foreach ($statistik as $s): ?>
          <div class="bg-slate-800 p-5 rounded-xl border border-slate-700">
            <div class="text-slate-400 text-xs font-medium uppercase mb-1"><?= e($s['label']) ?></div>
            <div class="text-3xl font-extrabold <?= e($s['warna']) ?>"><?= (int) $s['nilai'] ?></div>
            <div class="text-xs text-slate-500 mt-1"><?= e($s['keterangan']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- ========== 3. TIMELINE MASA BERLAKU (dari method getTimeline) ========== -->
    <section class="mb-8 bg-slate-800 border border-slate-700 rounded-2xl p-6 sm:p-8 shadow-xl">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
          <i class="fa-solid fa-timeline text-emerald-400"></i> Timeline Masa Berlaku Pass
        </h2>
        <span class="text-xs text-slate-400">Periode <?= e($profile['periode']) ?></span>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Data tahun dihasilkan oleh method getTimeline() (loop FOR di dalam class) -->
        <div class="grid grid-cols-2 gap-3 lg:col-span-2">
          <?php foreach ($timeline as $t): ?>
            <div class="rounded-xl border <?= e($t['warna']) ?> <?= $t['aktif'] ? 'ring-2 ring-emerald-500/40' : '' ?> px-4 py-3 text-center">
              <div class="text-lg font-extrabold text-white"><?= (int) $t['tahun'] ?></div>
              <div class="text-[11px] font-semibold mt-1 flex items-center justify-center gap-1">
                <i class="fa-solid <?= e($t['ikon']) ?>"></i><?= e($t['label']) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Ringkasan masa berlaku -->
        <div class="rounded-xl bg-slate-900/60 border border-slate-700 p-5 flex flex-col justify-center">
          <div class="text-xs text-slate-500 uppercase tracking-wide mb-1">Masa Berlaku Terpakai</div>
          <div class="text-3xl font-extrabold text-white mb-3"><?= (int) $profile['persenValid'] ?>%</div>
          <div class="h-2 w-full rounded-full bg-slate-700 overflow-hidden">
            <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-400" style="width: <?= (int) $profile['persenValid'] ?>%"></div>
          </div>
          <div class="text-[11px] text-slate-500 mt-2">
            <?= (int) $profile['masaBerlaku'] - (int) $profile['sisaTahun'] ?> dari <?= (int) $profile['masaBerlaku'] ?> tahun &#183; sisa <?= (int) $profile['sisaTahun'] ?> tahun
          </div>
        </div>
      </div>
    </section>

    <!-- ========== 4. PROGRAM PRIORITAS (dari method getPrograms) ========== -->
    <section id="program" class="mb-8">
      <div class="mb-6">
        <h2 class="text-2xl font-bold text-white flex items-center gap-2">
          <i class="fa-solid fa-layer-group text-emerald-400"></i> Program / Jalur Prioritas
        </h2>
        <p class="text-slate-400 text-sm mt-1">
          Alokasi kuota setiap program di-generate otomatis oleh method
          <code class="text-emerald-400">$pass-&gt;getPrograms()</code> (total kuota <?= (int) $totalKuota ?> slot).
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($programs as $p): ?>
            <article class="bg-slate-800 rounded-2xl border border-slate-700 shadow-lg flex flex-col hover:border-emerald-500/50 transition duration-300" aria-label="Program <?= e($p['nama']) ?>">
              <div class="p-5 flex-1 flex flex-col">
                <!-- Header kartu -->
                <div class="flex items-start justify-between mb-4">
                  <div class="w-12 h-12 rounded-xl <?= e($p['warnaKelas']) ?> flex items-center justify-center text-xl shrink-0">
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
                  <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?= e($p['badgeKelas']) ?>">
                    <?= e($p['badgeLabel']) ?>
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
                  <div class="h-full rounded-full <?= e($p['progressKelas']) ?>" style="width: <?= (int) $p['persen'] ?>%"></div>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-[10px] text-slate-400">Sisa slot</span>
                  <span class="text-sm font-extrabold text-white"><?= (int) $p['sisaSlot'] ?> slot</span>
                </div>
              </div>
            </article>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- ========== 5. JALUR / GUNUNG (dari method filterJalur) ========== -->
    <section id="jalur" class="mb-8">
      <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
        <div>
          <h2 class="text-2xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-mountain text-emerald-400"></i> Jalur &amp; Gunung Pendakian
          </h2>
          <p class="text-slate-400 text-sm mt-1">
            Kartu di-generate oleh method
            <code class="text-emerald-400">$pass-&gt;filterJalur()</code>
            (<?= count($jalurTampil) ?> dari <?= (int) $totalJalur ?> data ditampilkan).
          </p>
        </div>

        <!-- Form filter diproses PHP (method GET) -->
        <form method="get" action="<?= $__direct ? '../../../index.php' : 'index.php' ?>" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
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
          <a href="<?= $__direct ? '../../../index.php' : 'index.php' ?>" class="btn-secondary px-3 py-2 text-xs">
            <i class="fa-solid fa-rotate-left mr-1"></i> Reset filter
          </a>
        </div>
      <?php endif; ?>

      <?php if (count($jalurTampil) === 0): ?>
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-12 text-center text-slate-400">
          <i class="fa-solid fa-mountain-sun text-3xl mb-3 block"></i>
          Tidak ada jalur yang cocok dengan filter.
        </div>
      <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php foreach ($jalurTampil as $g): ?>
              <article class="bg-slate-800 rounded-2xl overflow-hidden border border-slate-700 shadow-lg flex flex-col hover:border-emerald-500/50 transition duration-300" aria-label="<?= e($g['nama']) ?> jalur <?= e($g['jalur']) ?>">
                <!-- Gambar + badge status -->
                <div class="relative h-48 overflow-hidden">
                  <img src="<?= e($g['gambar']) ?>" alt="<?= e($g['nama']) ?>" class="w-full h-full object-cover">
                  <span class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-bold <?= e($g['badgeKelas']) ?> shadow">
                    <?= e($g['badgeLabel']) ?>
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
                      <div class="h-full rounded-full <?= e($g['progressKelas']) ?>" style="width: <?= (int) $g['persen'] ?>%"></div>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                      <div>
                        <span class="text-[10px] text-slate-400 block">Tiket Masuk:</span>
                        <span class="text-base font-extrabold text-white"><?= e($g['hargaTeks']) ?></span>
                      </div>
                      <button type="button" <?= $g['isClosed'] ? 'disabled class="btn-secondary px-3 py-2 text-xs opacity-50 cursor-not-allowed"' : 'class="btn-primary px-3 py-2 text-xs"' ?>>
                        Pesan Tiket
                      </button>
                    </div>
                  </div>
                </div>
              </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <!-- ========== 6. RIWAYAT E-TICKET (dari method filterTiket) ========== -->
    <section id="riwayat" class="mb-8">
      <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
        <div>
          <h2 class="text-2xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-emerald-400"></i> Riwayat E-Ticket
          </h2>
          <p class="text-slate-400 text-sm mt-1">
            Riwayat tiket <?= e($profile['nama']) ?> di-generate oleh method
            <code class="text-emerald-400">$pass-&gt;filterTiket()</code>
            (<?= count($tiketTampil) ?> dari <?= (int) $totalTiket ?> data ditampilkan).
          </p>
        </div>

        <!-- Filter status tiket (diproses PHP) -->
        <form method="get" action="<?= $__direct ? '../../../index.php' : 'index.php' ?>" class="w-full sm:w-auto">
          <input type="hidden" name="status" value="<?= e($filterStatus) ?>">
          <input type="hidden" name="q" value="<?= e($filterCari) ?>">
          <select name="tiket" class="input-field w-auto" onchange="this.form.submit()">
            <option value="all" <?= $filterTiket === 'all' ? 'selected' : '' ?>>Semua Status Tiket</option>
            <option value="Aktif" <?= $filterTiket === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="Selesai" <?= $filterTiket === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
          </select>
        </form>
      </div>

      <?php if (count($tiketTampil) === 0): ?>
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-12 text-center text-slate-400">
          <i class="fa-solid fa-ticket text-3xl mb-3 block"></i>
          Tidak ada tiket yang cocok dengan filter.
        </div>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($tiketTampil as $t): ?>
            <article class="bg-slate-800 border border-slate-700 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center gap-4 justify-between hover:border-emerald-500/40 transition" aria-label="Tiket <?= e($t['kode']) ?>">
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
                <span class="text-sm font-bold text-slate-200"><?= e($t['hargaTeks']) ?></span>
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?= e($t['badgeKelas']) ?>">
                  <?= e($t['status']) ?>
                </span>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

  </main>

  <!-- ================= FOOTER ================= -->
  <footer class="bg-slate-950 border-t border-slate-800 py-6 mt-12 text-center text-xs text-slate-500">
    <div class="max-w-7xl mx-auto px-4">
      <p>&#169; <?= (int) date('Y') ?> HikePass Inc. Platform Pemesanan Tiket Pendakian Terorganisir.</p>
      <p class="mt-1">Halaman ini di-render oleh object <code class="text-emerald-400">HikePass</code> &#8212; seluruh data berasal dari method class, HTML di-generate otomatis.</p>
    </div>
  </footer>

</body>
</html>

