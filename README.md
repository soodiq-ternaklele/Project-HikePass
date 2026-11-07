<<<<<<< HEAD
# HikePass

Sistem pemesanan tiket pendakian gunung berbasis web. Project ini dibuat untuk memenuhi tugas **Pemrograman Web Pertemuan 1–7**: mulai dari Product Canvas, HTML/CSS responsif, JavaScript/DOM, API, PHP procedural, PHP OOP, sampai arsitektur MVC.

## Fitur

- Eksplorasi gunung dan jalur pendakian
- Cek kuota & jadwal
- Booking tiket 3 langkah + simulasi pembayaran
- E-ticket dengan QR Code dan cetak/PDF
- Riwayat tiket
- Mode ranger untuk verifikasi tiket
- Mode admin untuk mengatur kuota dan status jalur
- Responsif desktop & mobile (termasuk bottom navigation)
- REST API JSON: `php/api/gunung.php`
- PHP MVC: Model, View, Controller

## Struktur Project

```
HikePass/
├── index.html                     # Frontend SPA (Week 2–4)
├── style.css                      # Custom CSS + komponen responsif
├── script.js                      # JavaScript, DOM, event, fetch API
├── gunung.json                    # Dataset gunung (fallback API)
├── docs/
│   └── PRODUCT-CANVAS.md          # Deliverable Week 1
└── php/
    ├── index.php                  # Front controller (Week 7)
    ├── index-array.php            # Versi procedural (Week 5)
    ├── app/
    │   ├── models/
    │   │   └── HikePass.php       # Model (Week 6–7)
    │   ├── controllers/
    │   │   └── HikePassController.php
    │   └── views/
    │       └── home.php           # View
    ├── api/
    │   └── gunung.php             # REST API JSON (Week 4)
    ├── includes/
    │   ├── data.php
    │   └── functions.php
    └── README.md
```

## Cara Menjalankan

### Frontend (HTML/JS)

Jalankan dari folder HikePass agar `fetch()` bekerja:

```bash
# PHP built-in server
C:\tools\php\php.exe -S 127.0.0.1:8000 -t .
```

Buka <http://127.0.0.1:8000/index.html>.

> Frontend memakai `php/api/gunung.php`; jika server PHP tidak aktif, otomatis fallback ke `gunung.json`.

### PHP MVC

Buka <http://127.0.0.1:8000/php/index.php>.

### PHP Procedural (referensi Week 5)

Buka <http://127.0.0.1:8000/php/index-array.php>.

### REST API

```bash
GET http://127.0.0.1:8000/php/api/gunung.php
GET http://127.0.0.1:8000/php/api/gunung.php?status=Open
GET http://127.0.0.1:8000/php/api/gunung.php?q=semeru
```

## Alur MVC

```
Request
  └─ php/index.php                 (front controller)
       └─ HikePassController       (menerima request, menyiapkan data)
            └─ HikePass (Model)    (mengelola data)
                 └─ app/views/home.php (menampilkan HTML)
```

## Pemenuhan Task Week 1–7

| Week | Task | Implementasi |
|---|---|---|
| 1 | Product Canvas + GitHub | `docs/PRODUCT-CANVAS.md`, repo Git |
| 2 | HTML semantic, CSS, responsive | `index.html`, `style.css`, mobile bottom nav |
| 3 | JavaScript, DOM, event | `script.js` (tab, modal, booking, QR, toast) |
| 4 | API, fetch, JSON, loading/error | `php/api/gunung.php`, `fetchGunungData()` |
| 5 | PHP procedural | `php/index-array.php` |
| 6 | PHP OOP + encapsulation | `php/app/models/HikePass.php` (private property + getter) |
| 7 | MVC | front controller + controller + model + view |

## Pengujian

```bash
# Cek sintaks PHP
C:\tools\php\php.exe -l php/index.php
C:\tools\php\php.exe -l php/app/controllers/HikePassController.php
C:\tools\php\php.exe -l php/app/models/HikePass.php
C:\tools\php\php.exe -l php/app/views/home.php
C:\tools\php\php.exe -l php/api/gunung.php
```

Target pengujian:
- Semua halaman PHP HTTP 200 dan tidak ada `Fatal error`.
- Data gunung tampil dari API.
- Layout 390px tidak memiliki horizontal overflow.
- Navigasi mobile dapat berpindah tab.
- Booking, pembayaran, e-ticket, ranger, dan admin berjalan tanpa error.
=======
# Pem.WEB-HikePass
Pemograman web project kelas K
>>>>>>> dd07faac101570ff5a2ac76dc43cb4ce90d28d07
