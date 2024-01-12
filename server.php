<?php
$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Cek apakah file atau direktori fisik ada di URL
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Panggil index.php dari aplikasi Laravel
require_once __DIR__ . '/public/index.php';
