# HikePass — Halaman PHP (Task 01, 02, 03)

Implementasi **server-side** dari aplikasi HikePass dengan **Object-Oriented Programming** dan **arsitektur MVC**.

- **Pertemuan 5** — versi array/variabel biasa → `index-array.php`
- **Pertemuan 6** — versi **class & object** → `app/models/HikePass.php`
- **Pertemuan 7** — versi **MVC** → `index.php` + `app/controllers` + `app/views`

Data contoh: **Aris Setiawan** · ID Pass **HKP-2026-0091** · Periode **2026 – 2027** ·
Kategori **Pendaki Jelajah** · Status **Aktif** · Total Kuota **50** slot.

> Prinsip utama: **Tidak ada data HikePass yang ditulis manual (hardcoded) di dalam HTML.**
> Seluruh isi halaman di-*generate* oleh PHP — pada versi utama, semua data berasal dari
> **object** `HikePass`.

---

## 1. Struktur Folder

```
HikePass/
├── index.html / script.js / style.css / gunung.json   ← aplikasi JS (Week 2–4)
└── php/
    ├── index.php                  ← FRONT CONTROLLER (Week 7 - MVC)
    ├── index-array.php            ← versi array/variabel (Pertemuan 5, referensi)
    ├── app/
    │   ├── models/
    │   │   └── HikePass.php       ← Model: class + private property + getter
    │   ├── controllers/
    │   │   └── HikePassController.php ← Controller: request → model → view
    │   └── views/
    │       └── home.php           ← View: HTML + data dari controller
    ├── api/
    │   └── gunung.php             ← REST API JSON (Week 4)
    ├── includes/
    │   ├── HikePass.php           ← shim kompatibilitas ke app/models
    │   ├── data.php               ← sumber data (1 array dataset)
    │   └── functions.php          ← fungsi bantu: e(), rupiah(), persen(), badge(), dll
    ├── assets/
    │   └── hikepass-fallback.css  ← gaya cadangan bila CDN Tailwind tidak tersedia
    ├── run-server.bat             ← shortcut menjalankan server bawaan PHP
    └── README.md
```

---

## 2. Cara Menjalankan

### Opsi A — PHP Built-in Server (paling cepat)

```bash
# Dari folder HikePass
C:\tools\php\php.exe -S 127.0.0.1:8000 -t .
```

| URL | Isi |
|---|---|
| <http://127.0.0.1:8000/php/index.php> | **Versi MVC (Pertemuan 7)** — utama |
| <http://127.0.0.1:8000/php/index-array.php> | Versi array (Pertemuan 5) |
| <http://127.0.0.1:8000/php/api/gunung.php> | REST API JSON |

Docroot diarahkan ke folder **HikePass** (bukan folder `php`) supaya `../style.css` tetap bisa diakses.

### Opsi B — Shortcut
Klik dua kali `php\run-server.bat`, lalu buka <http://127.0.0.1:8000/php/index.php>

### Opsi C — XAMPP / Laragon
1. Salin folder `HikePass` ke `C:\xampp\htdocs\`
2. Jalankan Apache
3. Buka <http://localhost/hikepass/php/index.php>

### Cek sintaks
```bash
C:\tools\php\php.exe -l php\index.php
C:\tools\php\php.exe -l php\app\models\HikePass.php
C:\tools\php\php.exe -l php\app\controllers\HikePassController.php
C:\tools\php\php.exe -l php\app\views\home.php
C:\tools\php\php.exe -l php\api\gunung.php
C:\tools\php\php.exe -l php\includes\data.php
```

---

## 3. Class `HikePass` (Pertemuan 6)

### 3.1 Properties (Data) — 15 property private (encapsulation)

| Property | Tipe | Nilai |
|---|---|---|
| `$nama` | string | Aris Setiawan |
| `$idPass` | string | HKP-2026-0091 |
| `$kategori` | string | Pendaki Jelajah |
| `$status` | string | Aktif |
| `$periode` | string | 2026 - 2027 |
| `$email` / `$nik` | string | aris.setiawan@student.ac.id / 3507120903030091 |
| `$periodeMulai` / `$periodeSelesai` | int | 2026 / 2027 |
| `$tahunBerjalan` | int | 2026 |
| `$totalKuota` | int | 50 |
| `$programs` | array | 3 program prioritas + alokasi kuota |
| `$jalur` | array | 4 jalur gunung |
| `$tiket` | array | 3 riwayat e-ticket |
| `$profile` | array | ringkasan detail profil |

> Semua property bersifat **private**. Data dari luar class diakses melalui **getter**
> (`getNama()`, `getIdPass()`, `getTotalKuota()`, dan seterusnya) — inilah penerapan
> **encapsulation** pada Pertemuan 6.

### 3.2 Methods (Behavior) — 19 method perilaku + 11 getter

| Method | Return | Fungsi |
|---|---|---|
| `getProfile()` | array | Profil pendaki + status pass **(wajib di spek)** |
| `getPrograms()` | array | Daftar program prioritas + kuota + badge **(wajib di spek)** |
| `getJalur()` | array | Daftar jalur gunung + sisa kuota + badge Open/Closed |
| `getTiket()` | array | Riwayat e-ticket + badge status |
| `getTimeline()` | array | Tahun masa berlaku (loop FOR) + label Berlalu/Aktif/Mendatang |
| `getStatusInfo()` | array | Label + warna badge + ikon status pass (conditional) |
| `getStatistik()` | array | Data 4 kartu statistik |
| `getKuotaTerpakai()` | int | 35 (loop foreach atas `$programs`) |
| `getSisaKuota()` | int | `totalKuota - kuotaTerpakai` → **15** |
| `getPersenTerpakai()` | float | 70.0 |
| `getMasaBerlaku()` / `getSisaTahun()` / `getPersenValid()` | int / int / float | 2 / 1 / 50 |
| `getInisial()` | string | "AS" |
| `getJalurAktif()` / `getTiketAktif()` | int | 3 / 2 |
| `filterJalur($status, $q)` | array | Filter status + pencarian jalur |
| `filterTiket($status, $q)` | array | Filter status + pencarian tiket |

### 3.3 Cara memakai object di halaman (alur MVC)

```php
// Front controller: php/index.php
require_once __DIR__ . '/app/controllers/HikePassController.php';

$controller = new HikePassController(require __DIR__ . '/includes/data.php');
$controller->index(); // Controller memanggil Model lalu me-render View
```

Di dalam Controller:

```php
$profile     = $this->model->getProfile();
$programs    = $this->model->getPrograms();
$statusInfo  = $this->model->getStatusInfo();
$timeline    = $this->model->getTimeline();
$statistik   = $this->model->getStatistik();
$jalurTampil = $this->model->filterJalur($filterStatus, $filterCari);
$tiketTampil = $this->model->filterTiket($filterTiket, $filterCari);
```

View (`app/views/home.php`) hanya menampilkan variabel yang sudah disiapkan Controller.

---

## 4. Pemetaan Checklist Tugas

### Task 01 — Create a Class ✅

| Checklist | Bukti |
|---|---|
| Class berhasil dibuat | `class HikePass` di `app/models/HikePass.php`, ReflectionClass → bisa di-instansiasi |
| Property ditentukan sesuai kebutuhan | 15 property private (lihat tabel 3.1) |
| Object berhasil dibuat (instansiasi) | `$pass = new HikePass($dataset);` → `instanceof HikePass = true` |
| Data dalam object dapat diakses | `$pass->getNama()`, `$pass->getIdPass()`, `$pass->getPeriode()`, `$pass->getStatus()` → "Aris Setiawan" / "HKP-2026-0091" / "2026 - 2027" / "Aktif" |

### Task 02 — Add Properties & Methods ✅

| Checklist | Bukti |
|---|---|
| Property dibuat sesuai kebutuhan | Lihat tabel 3.1 |
| Method berhasil dibuat | 19 method publik |
| Method dapat dipanggil tanpa error | 19/19 method dipanggil tanpa exception (0 gagal) |
| Data dari method/property dipakai halaman | `getProfile()`, `getPrograms()`, `getStatusInfo()`, `getTimeline()`, `getStatistik()`, `filterJalur()`, `filterTiket()` dipanggil Controller dan dikirim ke View |

### Task 03 — Generate Dynamic Content with Object ✅

| Checklist | Bukti |
|---|---|
| Object PHP digunakan dalam halaman | `HikePassController` memakai object `HikePass`; View menerima data siap tampil |
| Method pada object dipanggil dan digunakan | Semua isi section berasal dari method |
| Seluruh data yang tampil berasal dari object | Tidak ada `$pendaki` / `$daftarProgram` / `$daftarGunung` / `$daftarTiket` di View |
| Kode HTML berhasil dihasilkan oleh server | 4 kartu gunung · 3 kartu program · 3 baris tiket · 2 baris timeline · 4 stat card |
| Tampilan visual tetap rapi sesuai desain UI | Output identik dengan versi array (regresi: jumlah elemen sama persis) |

---

## 5. Filter Pencarian (diproses server, lewat method object)

| Parameter | Nilai | Fungsi |
|---|---|---|
| `?status=` | `all` / `Open` / `Closed` | filter status jalur gunung |
| `?q=` | teks bebas | cari nama gunung, jalur, lokasi, kode tiket |
| `?tiket=` | `all` / `Aktif` / `Selesai` | filter status riwayat tiket |

```
/php/index.php?status=Closed      -> 1 kartu gunung (Gunung Gede)
/php/index.php?status=Open        -> 3 kartu gunung
/php/index.php?q=rinjani          -> 1 kartu gunung + 1 baris tiket
/php/index.php?tiket=Aktif        -> 2 baris tiket
/php/index.php?q=xyz              -> empty state
```

---

## 6. Catatan Teknis

- **Keamanan output** — data yang masuk HTML selalu melewati `e()`
  (`htmlspecialchars` + `ENT_QUOTES`), aman dari XSS.
- **Tanpa ekstensi tambahan** — hanya memakai fungsi inti PHP, jalan di instalasi
  standar tanpa `php.ini` khusus.
- **Single source of truth** — data hanya ada di `includes/data.php`; versi OOP
  (`new HikePass(require data.php)`) dan versi array memakai data yang sama.
- **Konsistensi kuota** — jumlah kuota di `$programs` (20 + 12 + 18) = `$totalKuota` (50),
  sehingga `getSisaKuota()` selalu konsisten dengan baris alokasi kuota.
- **Warna badge otomatis** — `kelasWarna()`: hijau (< 60%), kuning (60–85%), merah (> 85%).
  Program Infrastruktur (16/18 = 89%) sengaja dibuat memunculkan badge merah.
- **HTML valid** — karakter khusus (`&`, `·`, `—`, `©`) ditulis sebagai entity,
  sehingga tidak ada warning parser.
