<?php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Only serve actual static files (not directories, not index.php)
$file = __DIR__ . '/public' . $uri;
if ($uri !== '/' && file_exists($file) && !is_dir($file) && $uri !== '/index.php') {
    return false;
}

// Route everything to Laravel
chdir(__DIR__ . '/public');
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/public/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/public/index.php';