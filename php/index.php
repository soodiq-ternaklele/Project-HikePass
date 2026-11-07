<?php
/**
 * ============================================================================
 *  HIKEPASS - FRONT CONTROLLER (PERTEMUAN 7 - MVC)
 * ============================================================================
 *  Titik masuk aplikasi. File ini hanya:
 *    1. Memuat dataset & controller.
 *    2. Meneruskan request ke Controller.
 *
 *  Tidak ada HTML dan tidak ada logika bisnis di sini.
 *
 *  Alur MVC:
 *      Request -> index.php -> HikePassController -> HikePass (Model) -> View
 * ============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/app/controllers/HikePassController.php';

$dataset    = require __DIR__ . '/includes/data.php';
$controller = new HikePassController($dataset);

/*
 * Routing sederhana:
 *   index.php                  -> index()
 *   index.php?action=filter    -> filter()
 *   index.php?action=show&...  -> show()
 */
$action = $_GET['action'] ?? 'index';
$action = is_string($action) ? $action : 'index';

switch ($action) {
    case 'filter':
        $controller->filter();
        break;

    case 'show':
        $section = $_GET['section'] ?? 'pass';
        $section = is_string($section) ? $section : 'pass';
        $controller->show($section);
        break;

    case 'index':
    default:
        $controller->index();
        break;
}
