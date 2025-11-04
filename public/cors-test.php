<?php

declare(strict_types=1);

$policy = $_GET['policy'] ?? 'mirror';
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedMethods = $_GET['methods'] ?? 'GET, POST, OPTIONS';
$allowedHeaders = $_GET['headers'] ?? 'Content-Type, Authorization, X-Requested-With';

$applyOrigin = function (?string $value) use ($origin): void {
    if (!$value) {
        return;
    }

    if ($value === '*') {
        header('Access-Control-Allow-Origin: *');
        header('Vary: Origin');
        return;
    }

    if ($origin === '') {
        return;
    }

    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
};

switch ($policy) {
    case 'open':
        $applyOrigin('*');
        break;
    case 'credentials':
        $applyOrigin($origin === '' ? null : $origin);
        header('Access-Control-Allow-Credentials: true');
        break;
    case 'strict':
        // no Access-Control-Allow-Origin header
        break;
    case 'mirror':
    default:
        $applyOrigin($origin === '' ? null : $origin);
        break;
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: ' . $allowedMethods);
    header('Access-Control-Allow-Headers: ' . $allowedHeaders);
    header('Access-Control-Max-Age: 600');
    header('Content-Length: 0');
    http_response_code(204);
    exit;
}

header('Access-Control-Expose-Headers: X-Debug-Policy');
header('X-Debug-Policy: ' . $policy);
header('Content-Type: application/json');

echo json_encode([
    'policy' => $policy,
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
    'origin_seen' => $origin,
    'allowed_methods' => $allowedMethods,
    'allowed_headers' => $allowedHeaders,
    'timestamp' => date('c'),
    'via' => $_SERVER['HTTP_VIA'] ?? null,
    'forwarded_for' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
    'server' => [
        'software' => $_SERVER['SERVER_SOFTWARE'] ?? null,
        'name' => $_SERVER['SERVER_NAME'] ?? null,
    ],
]);
