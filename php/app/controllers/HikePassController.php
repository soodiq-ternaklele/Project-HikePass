<?php
/**
 * ============================================================================
 *  HIKEPASS - CONTROLLER (PERTEMUAN 7 - MVC)
 * ============================================================================
 *  Controller menerima request, meminta data ke Model, lalu mengirim data
 *  tersebut ke View. Controller TIDAK berisi HTML.
 *
 *  Alur:
 *      Request ($_GET) -> HikePassController -> HikePass (Model) -> View
 * ============================================================================
 */

require_once __DIR__ . '/../models/HikePass.php';

class HikePassController
{
    private HikePass $model;

    public function __construct(array $dataset)
    {
        $this->model = new HikePass($dataset);
    }

    /**
     * Halaman utama (request default).
     */
    public function index(): void
    {
        $this->render($this->buildViewData($this->readFilters()));
    }

    /**
     * Menangani request filter/pencarian dari form GET.
     * Method ini menjaga agar logika request tetap berada di Controller.
     */
    public function filter(): void
    {
        $this->render($this->buildViewData($this->readFilters()));
    }

    /**
     * Menampilkan section tertentu (dipakai untuk navigasi #anchor).
     * View tetap satu halaman; parameter ini disiapkan untuk pengembangan.
     */
    public function show(string $section = 'pass'): void
    {
        $data = $this->buildViewData($this->readFilters());
        $data['activeSection'] = $section;
        $this->render($data);
    }

    /**
     * Membaca input request dengan aman.
     */
    private function readFilters(): array
    {
        $status = $_GET['status'] ?? 'all';
        $cari   = $_GET['q'] ?? '';
        $tiket  = $_GET['tiket'] ?? 'all';

        $status = is_string($status) ? $status : 'all';
        $cari   = is_string($cari) ? trim($cari) : '';
        $tiket  = is_string($tiket) ? $tiket : 'all';

        if (!in_array($status, ['all', 'Open', 'Closed'], true)) {
            $status = 'all';
        }
        if (!in_array($tiket, ['all', 'Aktif', 'Selesai'], true)) {
            $tiket = 'all';
        }

        return [
            'status' => $status,
            'cari'   => $cari,
            'tiket'  => $tiket,
        ];
    }

    /**
     * Menyiapkan seluruh data yang dibutuhkan View.
     */
    private function buildViewData(array $filters): array
    {
        $filterStatus = $filters['status'];
        $filterCari   = $filters['cari'];
        $filterTiket  = $filters['tiket'];

        return [
            'filterStatus' => $filterStatus,
            'filterCari'   => $filterCari,
            'filterTiket'  => $filterTiket,
            'filterAktif'  => ($filterStatus !== 'all' || $filterCari !== '' || $filterTiket !== 'all'),
            'profile'      => $this->model->getProfile(),
            'programs'     => $this->model->getPrograms(),
            'statusInfo'   => $this->model->getStatusInfo(),
            'timeline'     => $this->model->getTimeline(),
            'statistik'    => $this->model->getStatistik(),
            'jalurTampil'  => $this->model->filterJalur($filterStatus, $filterCari),
            'tiketTampil'  => $this->model->filterTiket($filterTiket, $filterCari),
            'totalJalur'   => count($this->model->getJalur()),
            'totalTiket'   => count($this->model->getTiket()),
            'totalKuota'   => $this->model->getTotalKuota(),
            'activeSection' => 'pass',
        ];
    }

    /**
     * Render View. Satu-satunya tempat yang tahu lokasi file View.
     */
    private function render(array $data): void
    {
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../views/home.php';
    }
}
