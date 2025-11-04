<?php

declare(strict_types=1);

$code = (int) ($_GET['code'] ?? 302);
$target = trim($_GET['target'] ?? 'index.php');

$allowedCodes = [301, 302, 303, 307, 308];
if (!in_array($code, $allowedCodes, true)) {
    $code = 302;
}

if ($target === '') {
    $target = 'index.php';
}

$scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ($_SERVER['REQUEST_SCHEME'] ?? ((isset($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') ? 'https' : 'http'));
$host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost');

if (!preg_match('#^https?://#i', $target)) {
    if ($target[0] !== '/') {
        $target = '/' . $target;
    }
    $target = $scheme . '://' . $host . $target;
}

header('Location: ' . $target, true, $code);
header('Cache-Control: no-store');
header('X-Debug-Redirect-Code: ' . $code);
header('X-Debug-Redirect-Target: ' . $target);

?><!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Redirection <?= htmlspecialchars((string) $code) ?></title>
</head>
<body>
    <p>Redirection <?= htmlspecialchars((string) $code) ?> vers <a href="<?= htmlspecialchars($target) ?>"><?= htmlspecialchars($target) ?></a>.</p>
</body>
</html>
