<?php

declare(strict_types=1);

$requested = $_GET['file'] ?? '';
$filename = basename($requested);
$uploadDir = realpath(__DIR__ . '/../uploads');

if ($filename === '' || $uploadDir === false) {
    http_response_code(404);
    echo 'Fichier non disponible.';
    exit;
}

$path = realpath($uploadDir . DIRECTORY_SEPARATOR . $filename);
if ($path === false || strpos($path, $uploadDir) !== 0 || !is_file($path)) {
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
header('Cache-Control: private, max-age=60');
header('X-Content-Type-Options: nosniff');

readfile($path);
