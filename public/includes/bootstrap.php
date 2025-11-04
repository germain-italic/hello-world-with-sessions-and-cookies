<?php

declare(strict_types=1);

$envPath = dirname(__DIR__) . '/../.env';
$env = [];
if (is_file($envPath)) {
    $env = parse_ini_file($envPath, false, INI_SCANNER_TYPED) ?: [];
}

define('PROXY_CONFIG', [
    'trusted_proxy_ips' => isset($env['TRUSTED_PROXY_IPS'])
        ? array_filter(array_map('trim', explode(',', (string) $env['TRUSTED_PROXY_IPS'])))
        : [],
]);

require_once __DIR__ . '/helpers.php';
