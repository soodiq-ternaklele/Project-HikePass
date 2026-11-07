<?php

require_once __DIR__ . '/../../includes/functions.php';

class HikePass
{
    /* =======================================================================
     *  PROPERTIES (DATA)
     *  Struktur data yang dibutuhkan oleh sistem HikePass.
     * ===================================================================== */
    private string $nama;             // Nama Pendaki
    private string $idPass;           // Nomor / ID Pass
    private string $kategori;         // Kategori Pass
    private string $status;           // Status Pass (Aktif / Kadaluarsa)
    private string $periode;          // Masa Berlaku Pass (contoh: "2026 - 2027")
    private string $email;            // Email pendaki
    private string $nik;              // NIK pendaki

    private int $periodeMulai;        // Tahun mulai berlaku
    private int $periodeSelesai;      // Tahun akhir berlaku
    private int $tahunBerjalan;       // Tahun berjalan pada skenario pass
    private int $totalKuota;          // Total kuota slot HikePass

    private array $programs;          // Daftar jalur / program prioritas
    private array $jalur;             // Daftar jalur / gunung pendakian
    private array $tiket;             // Riwayat e-ticket pendaki

    private array $profile;           // Ringkasan detail profil & status pass

    /* =======================================================================
     *  CONSTRUCTOR - OBJECT INSTANTIATION (TASK 01)
     *  Menerima dataset dari includes/data.php lalu mengisi seluruh property.
     * ===================================================================== */
    public function __construct(array $data)
    {
        $this->nama           = $data['nama'];
        $this->idPass         = $data['idPass'];
        $this->kategori       = $data['kategori'];
        $this->status         = $data['status'];
        $this->email          = $data['email'];
        $this->nik            = $data['nik'];
        $this->periodeMulai   = $data['periodeMulai'];
        $this->periodeSelesai = $data['periodeSelesai'];
        $this->tahunBerjalan  = $data['tahunBerjalan'];
        $this->totalKuota     = $data['totalKuota'];

        $this->programs = $data['programs'];
        $this->jalur    = $data['jalur'];
        $this->tiket    = $data['tiket'];

        // Periode pass disusun dari tahun mulai & akhir
        $this->periode = $this->periodeMulai . ' - ' . $this->periodeSelesai;

        // Profil dirangkai otomatis oleh behavior internal
        $this->profile = $this->buildProfile();
    }

    /* =======================================================================
     *  GETTERS - ENCAPSULATION (PERTEMUAN 6)
     *  Property dibuat private; akses data dari luar class hanya lewat method.
     * ===================================================================== */
    public function getNama(): string
    {
        return $this->nama;
    }

    public function getIdPass(): string
    {
        return $this->idPass;
    }

    public function getKategori(): string
    {
        return $this->kategori;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPeriode(): string
    {
        return $this->periode;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getNik(): string
    {
        return $this->nik;
    }

    public function getPeriodeMulai(): int
    {
        return $this->periodeMulai;
    }

    public function getPeriodeSelesai(): int
    {
        return $this->periodeSelesai;
    }

    public function getTahunBerjalan(): int
    {
        return $this->tahunBerjalan;
    }

    public function getTotalKuota(): int
    {
        return $this->totalKuota;
    }

    /* =======================================================================
     *  METHODS (BEHAVIOR) - TASK 02
     * ===================================================================== */

    /**
     * getProfile() - Mengembalikan data profil pendaki beserta status pass.
     */
    public function getProfile(): array
    {
        return $this->profile;
    }

    /**
     * getPrograms() - Mengembalikan daftar program/jalur prioritas lengkap
     * dengan hasil perhitungan kuota dan badge statusnya.
     */
    public function getPrograms(): array
    {
        $hasil = [];

        foreach ($this->programs as $p) {
            $persen = persen($p['terpakai'], $p['kuota']);

            $hasil[] = [
                'nama'         => $p['nama'],
                'jalur'        => $p['jalur'],
                'prioritas'    => $p['prioritas'],
                'kategori'     => $p['kategori'],
                'kuota'        => $p['kuota'],
                'terpakai'     => $p['terpakai'],
                'sisaSlot'     => $p['kuota'] - $p['terpakai'],
                'persen'       => $persen,
                'status'       => $p['status'],
                'ikon'         => $p['ikon'],
                'warna'        => $p['warna'],
                'warnaKelas'   => kelasIkonWarna($p['warna']),
                'badgeKelas'   => kelasWarna($persen),
                'badgeLabel'   => labelKuota($persen),
                'progressKelas' => kelasProgress($persen),
                'deskripsi'    => $p['deskripsi'],
            ];
        }

        return $hasil;
    }

    /**
     * getJalur() - Mengembalikan daftar jalur pendakian lengkap dengan
     * sisa kuota, warna badge status Open/Closed, dan status tombol.
     */
    public function getJalur(): array
    {
        $hasil = [];

        foreach ($this->jalur as $g) {
            $kuotaTerpakai = $g['kuotaMaks'] - $g['sisaKuota'];
            $persen        = persen($kuotaTerpakai, $g['kuotaMaks']);

            $hasil[] = [
                'id'            => $g['id'],
                'nama'          => $g['nama'],
                'jalur'         => $g['jalur'],
                'lokasi'        => $g['lokasi'],
                'ketinggian'    => $g['ketinggian'],
                'harga'         => $g['harga'],
                'hargaTeks'     => rupiah($g['harga']),
                'kuotaMaks'     => $g['kuotaMaks'],
                'sisaKuota'     => $g['sisaKuota'],
                'kuotaTerpakai' => $kuotaTerpakai,
                'persen'        => $persen,
                'status'        => $g['status'],
                'isClosed'      => ($g['status'] === 'Closed' || $g['sisaKuota'] <= 0),
                'badgeKelas'    => badgeJalur($g['status']),
                'badgeLabel'    => labelJalur($g['status']),
                'progressKelas' => kelasProgress($persen),
                'gambar'        => $g['gambar'],
                'deskripsi'     => $g['deskripsi'],
                'persyaratan'   => $g['persyaratan'],
            ];
        }

        return $hasil;
    }

    /**
     * getTiket() - Mengembalikan riwayat e-ticket pendaki.
     */
    public function getTiket(): array
    {
        $hasil = [];

        foreach ($this->tiket as $t) {
            $hasil[] = [
                'kode'       => $t['kode'],
                'gunung'     => $t['gunung'],
                'jalur'      => $t['jalur'],
                'tanggal'    => $t['tanggal'],
                'status'     => $t['status'],
                'badgeKelas' => badgeTiket($t['status']),
                'harga'      => $t['harga'],
                'hargaTeks'  => rupiah($t['harga']),
            ];
        }

        return $hasil;
    }

    /**
     * getTimeline() - Menghasilkan daftar tahun masa berlaku pass.
     * Memakai perulangan FOR di dalam method.
     */
    public function getTimeline(): array
    {
        $timeline = [];

        for ($tahun = $this->periodeMulai; $tahun <= $this->periodeSelesai; $tahun++) {
            // Conditional penentu status setiap tahun
            if ($tahun < $this->tahunBerjalan) {
                $label = 'Berlalu';
                $warna = 'bg-slate-700 text-slate-400 border-slate-700';
                $ikon  = 'fa-circle-check';
            } elseif ($tahun === $this->tahunBerjalan) {
                $label = 'Aktif';
                $warna = 'bg-emerald-500/15 text-emerald-300 border-emerald-500/40';
                $ikon  = 'fa-circle-dot';
            } else {
                $label = 'Mendatang';
                $warna = 'bg-slate-800 text-slate-500 border-slate-700';
                $ikon  = 'fa-circle';
            }

            $timeline[] = [
                'tahun' => $tahun,
                'label' => $label,
                'warna' => $warna,
                'ikon'  => $ikon,
                'aktif' => ($tahun === $this->tahunBerjalan),
            ];
        }

        return $timeline;
    }

    /**
     * getStatusInfo() - Conditional warna & label badge status pass.
     */
    public function getStatusInfo(): array
    {
        $sisaTahun = $this->getSisaTahun();

        if ($this->status == 'Aktif' && $sisaTahun > 0) {
            $label = 'Berjalan / Aktif';
            $kelas = 'bg-emerald-500 text-slate-950';
            $ikon  = 'fa-circle-check';
        } elseif ($this->status == 'Kadaluarsa' || $sisaTahun <= 0) {
            $label = 'Pass Kadaluarsa';
            $kelas = 'bg-red-500 text-white';
            $ikon  = 'fa-circle-xmark';
        } else {
            $label = 'Segera Habis';
            $kelas = 'bg-amber-500 text-slate-950';
            $ikon  = 'fa-triangle-exclamation';
        }

        return [
            'label'  => $label,
            'kelas'  => $kelas,
            'ikon'   => $ikon,
            'status' => $this->status,
        ];
    }

    /**
     * getKuotaTerpakai() - Total kuota yang sudah terpakai (loop foreach).
     */
    public function getKuotaTerpakai(): int
    {
        $total = 0;

        foreach ($this->programs as $p) {
            $total += $p['terpakai'];
        }

        return $total;
    }

    /**
     * getSisaKuota() - Sisa kuota = total kuota - kuota terpakai.
     */
    public function getSisaKuota(): int
    {
        return $this->totalKuota - $this->getKuotaTerpakai();
    }

    /**
     * getPersenTerpakai() - Persentase pemakaian kuota.
     */
    public function getPersenTerpakai(): float
    {
        return persen($this->getKuotaTerpakai(), $this->totalKuota);
    }

    /**
     * getMasaBerlaku() - Jumlah tahun masa berlaku pass.
     */
    public function getMasaBerlaku(): int
    {
        return $this->periodeSelesai - $this->periodeMulai + 1;
    }

    /**
     * getSisaTahun() - Sisa masa aktif pass dalam tahun.
     */
    public function getSisaTahun(): int
    {
        return $this->periodeSelesai - $this->tahunBerjalan;
    }

    /**
     * getPersenValid() - Persentase masa berlaku pass yang sudah berjalan.
     */
    public function getPersenValid(): float
    {
        $terpakai = $this->tahunBerjalan - $this->periodeMulai + 1;
        $masa     = $this->getMasaBerlaku();

        return $masa > 0 ? round(($terpakai / $masa) * 100) : 0;
    }

    /**
     * getInisial() - Inisial nama untuk avatar.
     */
    public function getInisial(): string
    {
        $inisial = '';

        foreach (array_slice(explode(' ', $this->nama), 0, 2) as $kata) {
            $inisial .= strtoupper(substr($kata, 0, 1));
        }

        return $inisial;
    }

    /**
     * getJalurAktif() - Jumlah jalur yang masih bisa dipesan.
     */
    public function getJalurAktif(): int
    {
        $jumlah = 0;

        foreach ($this->jalur as $g) {
            if ($g['status'] === 'Open' && $g['sisaKuota'] > 0) {
                $jumlah++;
            }
        }

        return $jumlah;
    }

    /**
     * getTiketAktif() - Jumlah e-ticket yang masih aktif.
     */
    public function getTiketAktif(): int
    {
        $jumlah = 0;

        foreach ($this->tiket as $t) {
            if ($t['status'] === 'Aktif') {
                $jumlah++;
            }
        }

        return $jumlah;
    }

    /**
     * getStatistik() - Data ringkasan untuk kartu statistik.
     */
    public function getStatistik(): array
    {
        return [
            [
                'label' => 'Total Kuota',
                'nilai' => $this->totalKuota,
                'warna' => 'text-white',
                'keterangan' => 'Slot pass HikePass',
            ],
            [
                'label' => 'Kuota Terpakai',
                'nilai' => $this->getKuotaTerpakai(),
                'warna' => 'text-amber-400',
                'keterangan' => (int) $this->getPersenTerpakai() . '% dari total kuota',
            ],
            [
                'label' => 'Sisa Kuota',
                'nilai' => $this->getSisaKuota(),
                'warna' => 'text-emerald-400',
                'keterangan' => $this->getJalurAktif() . ' jalur masih dibuka',
            ],
            [
                'label' => 'Tiket Aktif',
                'nilai' => $this->getTiketAktif(),
                'warna' => 'text-sky-400',
                'keterangan' => 'dari ' . count($this->tiket) . ' riwayat tiket',
            ],
        ];
    }

    /**
     * filterJalur() - Filter & pencarian daftar jalur (diproses di server).
     *
     * @param string $status 'all' | 'Open' | 'Closed'
     * @param string $q      Kata kunci pencarian
     */
    public function filterJalur(string $status = 'all', string $q = ''): array
    {
        $hasil = [];
        $q     = trim($q);

        foreach ($this->getJalur() as $g) {
            if ($status !== 'all' && $g['status'] !== $status) {
                continue;
            }
            if ($q !== '') {
                $teks = strtolower($g['nama'] . ' ' . $g['jalur'] . ' ' . $g['lokasi']);
                if (strpos($teks, strtolower($q)) === false) {
                    continue;
                }
            }
            $hasil[] = $g;
        }

        return $hasil;
    }

    /**
     * filterTiket() - Filter & pencarian riwayat e-ticket.
     *
     * @param string $status 'all' | 'Aktif' | 'Selesai'
     * @param string $q      Kata kunci pencarian
     */
    public function filterTiket(string $status = 'all', string $q = ''): array
    {
        $hasil = [];
        $q     = trim($q);

        foreach ($this->getTiket() as $t) {
            if ($status !== 'all' && $t['status'] !== $status) {
                continue;
            }
            if ($q !== '') {
                $teks = strtolower($t['kode'] . ' ' . $t['gunung'] . ' ' . $t['jalur']);
                if (strpos($teks, strtolower($q)) === false) {
                    continue;
                }
            }
            $hasil[] = $t;
        }

        return $hasil;
    }

    /* =======================================================================
     *  PRIVATE HELPER - Merangkai ringkasan profil
     * ===================================================================== */
    private function buildProfile(): array
    {
        return [
            'nama'        => $this->nama,
            'idPass'      => $this->idPass,
            'kategori'    => $this->kategori,
            'periode'     => $this->periode,
            'status'      => $this->status,
            'email'       => $this->email,
            'nik'         => $this->nik,
            'inisial'     => $this->getInisial(),
            'masaBerlaku' => $this->getMasaBerlaku(),
            'sisaTahun'   => $this->getSisaTahun(),
            'persenValid' => $this->getPersenValid(),
        ];
    }
}
