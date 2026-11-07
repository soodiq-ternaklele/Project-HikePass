<?php
/**
 *  Alur MVC:
 *      Request -> index.php -> HikePassController -> HikePass (Model) -> View
 * ============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/app/controllers/HikePassController.php';

$dataset    = require __DIR__ . '/includes/data.php';
$controller = new HikePassController($dataset);

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
