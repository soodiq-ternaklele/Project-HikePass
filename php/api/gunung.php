<?php
/**
 * ============================================================================
 *  HIKEPASS - REST API: DAFTAR GUNUNG (PERTEMUAN 4)
 * ============================================================================
 *  Endpoint JSON sederhana untuk aplikasi frontend.
 *
 *  GET /php/api/gunung.php
 *  GET /php/api/gunung.php?status=Open
 *  GET /php/api/gunung.php?q=semeru
 * ============================================================================
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');

$jsonPath = dirname(__DIR__, 2) . '/gunung.json';

if (!is_file($jsonPath)) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Data gunung tidak ditemukan.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$raw  = file_get_contents($jsonPath);
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Format data gunung tidak valid.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$status = isset($_GET['status']) && is_string($_GET['status']) ? $_GET['status'] : 'all';
$query  = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';

if ($status !== 'all') {
    $data = array_values(array_filter($data, static function (array $gunung) use ($status): bool {
        return ($gunung['status'] ?? '') === $status;
    }));
}

if ($query !== '') {
    $needle = strtolower($query);
    $data = array_values(array_filter($data, static function (array $gunung) use ($needle): bool {
        $haystack = strtolower(implode(' ', [
            $gunung['nama'] ?? '',
            $gunung['jalur'] ?? '',
            $gunung['lokasi'] ?? '',
        ]));

        return str_contains($haystack, $needle);
    }));
}

echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
