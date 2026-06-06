<?php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve existing files directly (assets, etc)
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

// Route everything else to Laravel's index.php
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/public/index.php';
require_once __DIR__ . '/public/index.php';
