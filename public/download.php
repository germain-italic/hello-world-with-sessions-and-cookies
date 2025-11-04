<?php

declare(strict_types=1);

$file = $_GET['file'] ?? '';
$filename = basename($file);
$allowed = ['proxy-test.txt'];

if (!in_array($filename, $allowed, true)) {
    http_response_code(404);
    echo 'Fichier non disponible.';
    exit;
}

$path = __DIR__ . '/../downloads/' . $filename;

if (!is_file($path)) {
    http_response_code(404);
    echo 'Fichier introuvable.';
    exit;
}

$mime = mime_content_type($path) ?: 'application/octet-stream';
$size = filesize($path);

header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Length: ' . $size);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Content-Type-Options: nosniff');

readfile($path);
