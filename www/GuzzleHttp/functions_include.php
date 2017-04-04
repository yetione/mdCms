<?php

// Don't redefine the functions if included multiple times.
if (!function_exists('GuzzleHttp\uri_template')) {
    require __DIR__ . '/functions.php';
}
require __DIR__ . '/Psr7/functions_include.php';
require __DIR__ . '/Promise/functions_include.php';
