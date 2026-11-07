# Product Canvas — HikePass

## 1. Nama Produk
**HikePass** — sistem pemesanan tiket pendakian gunung berbasis web.

## 2. Pengguna
| Pengguna | Kebutuhan utama |
|---|---|
| Pendaki / wisatawan | Mencari jalur, melihat kuota, memesan tiket, mendapat e-ticket |
| Ranger pos | Memverifikasi tiket dan mencatat check-in/check-out pendaki |
| Admin pengelola | Mengatur kuota, membuka/menutup jalur, memantau pendaki |

## 3. Masalah
- Pemesanan tiket masih dilakukan manual di pos pendaftaran.
- Pendaki tidak tahu sisa kuota secara real-time.
- Tiket kertas mudah hilang dan sulit diverifikasi.
- Antrean di pos pendakian panjang, terutama akhir pekan.
- Data pendaki yang naik/turun tidak terpantau dengan baik.

## 4. Solusi
HikePass menyediakan satu platform web untuk:
- Melihat daftar gunung dan jalur pendakian.
- Melihat kuota harian secara real-time.
- Memesan tiket secara online dengan data ketua dan anggota rombongan.
- Mendapatkan e-ticket ber-QR Code.
- Verifikasi tiket oleh ranger di pos.
- Pengelolaan kuota dan status jalur oleh admin.

## 5. Produk
Aplikasi web responsif dengan dua bagian:
1. **Frontend** — HTML, CSS (Tailwind + custom), JavaScript, DOM, fetch API.
2. **Backend** — PHP OOP dengan arsitektur MVC, data server-side, dan REST API JSON.

## 6. Fitur Wajib
- [x] Eksplorasi gunung dan jalur pendakian
- [x] Pencarian dan filter jalur
- [x] Cek kuota & jadwal
- [x] Booking tiket 3 langkah (destinasi → data rombongan → pembayaran)
- [x] Simulasi pembayaran (QRIS / VA / e-wallet)
- [x] E-ticket dengan QR Code dan cetak/PDF
- [x] Riwayat tiket
- [x] Mode ranger: verifikasi kode booking
- [x] Mode admin: kelola kuota dan status jalur
- [x] Tampilan responsif desktop & mobile
- [x] API JSON untuk data gunung
- [x] PHP MVC (Model, View, Controller)

## 7. Fitur di Luar Scope (versi awal)
- Pembayaran nyata (payment gateway)
- Login/registrasi dengan database
- Notifikasi email/WhatsApp
- Aplikasi mobile native
- Multi-bahasa

## 8. Ukuran Keberhasilan
- Pengguna dapat menyelesaikan booking tanpa error.
- Data gunung tampil dari API, bukan hardcoded di HTML.
- Halaman dapat dipakai di layar 390px tanpa horizontal overflow.
- Ranger dapat memverifikasi tiket dengan kode booking.
- Admin dapat mengubah kuota/status jalur.
- Semua halaman PHP berjalan tanpa error.

## 9. Peran Tim (contoh)
| Peran | Tanggung jawab |
|---|---|
| Developer | HTML, CSS, JavaScript, PHP, API |
| UI/UX | Desain halaman, alur booking, responsif |
| Product/Project | Product Canvas, prioritas fitur, demo |
| Tester | Uji form, API state, responsive, error handling |
