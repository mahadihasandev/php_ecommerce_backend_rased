<?php

// Force SCRIPT_NAME and PHP_SELF to /index.php so Symfony Request does not strip /api from request URIs
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

// Prepare serverless environment for Vercel
if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || getenv('VERCEL')) {
    $tmpDir = sys_get_temp_dir();
    
    // Copy bootstrap packages/services to /tmp so Laravel has writable cache
    if (!file_exists($tmpDir . '/packages.php') && file_exists(__DIR__ . '/../bootstrap/cache/packages.php')) {
        @copy(__DIR__ . '/../bootstrap/cache/packages.php', $tmpDir . '/packages.php');
    }
    if (!file_exists($tmpDir . '/services.php') && file_exists(__DIR__ . '/../bootstrap/cache/services.php')) {
        @copy(__DIR__ . '/../bootstrap/cache/services.php', $tmpDir . '/services.php');
    }

    // Ensure dynamic database config is always evaluated freshly
    if (file_exists($tmpDir . '/config.php')) {
        @unlink($tmpDir . '/config.php');
    }

    // Set environment variables for cache locations
    putenv("APP_PACKAGES_CACHE={$tmpDir}/packages.php");
    putenv("APP_SERVICES_CACHE={$tmpDir}/services.php");
    putenv("VIEW_COMPILED_PATH={$tmpDir}/views");

    if (!is_dir($tmpDir . '/views')) {
        @mkdir($tmpDir . '/views', 0755, true);
    }
}

// Forward all incoming Vercel serverless requests to public/index.php
require __DIR__ . '/../public/index.php';
