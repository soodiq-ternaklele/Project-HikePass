<?php
/**
 * Compatibility shim.
 *
 * Sejak Pertemuan 7 (MVC), class HikePass berada di:
 *   php/app/models/HikePass.php
 *
 * File ini dipertahankan agar kode lama yang masih memanggil
 * includes/HikePass.php tetap berjalan.
 */

require_once __DIR__ . '/../app/models/HikePass.php';
