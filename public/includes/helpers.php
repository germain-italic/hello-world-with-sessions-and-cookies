<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Start the PHP session if needed.
 */
function ensure_session_started(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function authenticate_with_session(string $username): void
{
    ensure_session_started();
    session_regenerate_id(true);
    $_SESSION['auth_user'] = $username;
    $_SESSION['auth_started_at'] = time();
}

function destroy_session(): void
{
    ensure_session_started();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function is_session_authenticated(): bool
{
    ensure_session_started();
    return isset($_SESSION['auth_user']);
}

function require_session_auth(): void
{
    if (!is_session_authenticated()) {
        header('Location: session-login.php?status=danger&message=' . urlencode('Veuillez vous connecter via la session PHP.'));
        exit;
    }
}

function get_session_details(): array
{
    ensure_session_started();

    return [
        'session_id' => session_id(),
        'auth_user' => $_SESSION['auth_user'] ?? null,
        'auth_started_at' => $_SESSION['auth_started_at'] ?? null,
        'custom_data' => $_SESSION['custom_data'] ?? [],
    ];
}

function set_session_custom_value(string $key, string $value): void
{
    ensure_session_started();
    $_SESSION['custom_data'][$key] = [
        'value' => $value,
        'updated_at' => time(),
    ];
}

function remove_session_custom_value(string $key): void
{
    ensure_session_started();
    unset($_SESSION['custom_data'][$key]);
}

function set_auth_cookie(string $username): void
{
    $payload = [
        'user' => $username,
        'issued_at' => time(),
        'proxy_hint' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
    ];

    $encoded = base64_encode(json_encode($payload, JSON_THROW_ON_ERROR));

    setcookie(
        COOKIE_AUTH_NAME,
        $encoded,
        [
            'expires' => time() + COOKIE_LIFETIME,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => false, // keep visible to test direct cookie manipulation
            'samesite' => 'Lax',
        ]
    );

    $_COOKIE[COOKIE_AUTH_NAME] = $encoded;
}

function has_cookie_auth(): bool
{
    $payload = get_cookie_payload();
    return isset($payload['user']);
}

function require_cookie_auth(): void
{
    if (!has_cookie_auth()) {
        header('Location: cookie-login.php?status=danger&message=' . urlencode('Veuillez ouvrir une session basée sur le cookie.'));
        exit;
    }
}

function get_cookie_payload(): array
{
    if (empty($_COOKIE[COOKIE_AUTH_NAME])) {
        return [];
    }

    $raw = base64_decode($_COOKIE[COOKIE_AUTH_NAME], true);
    if ($raw === false) {
        return [];
    }

    try {
        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (Throwable) {
        return [];
    }

    return is_array($decoded) ? $decoded : [];
}

function refresh_auth_cookie(array $payload): void
{
    $payload['refreshed_at'] = time();
    $encoded = base64_encode(json_encode($payload, JSON_THROW_ON_ERROR));

    setcookie(
        COOKIE_AUTH_NAME,
        $encoded,
        [
            'expires' => time() + COOKIE_LIFETIME,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => false,
            'samesite' => 'Lax',
        ]
    );

    $_COOKIE[COOKIE_AUTH_NAME] = $encoded;
}

function clear_auth_cookie(): void
{
    setcookie(COOKIE_AUTH_NAME, '', time() - 3600, '/');
    unset($_COOKIE[COOKIE_AUTH_NAME]);
}

function redirect_with_status(string $target, string $status, string $message): void
{
    header('Location: ' . $target . '?status=' . urlencode($status) . '&message=' . urlencode($message));
    exit;
}

function current_status_message(): array
{
    $status = $_GET['status'] ?? null;
    $message = $_GET['message'] ?? null;

    if (!$status || !$message) {
        return [];
    }

    return [
        'type' => htmlspecialchars($status, ENT_QUOTES, 'UTF-8'),
        'text' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
    ];
}

function render_header(string $title, string $active = ''): void
{
    $status = current_status_message();
    ?>
    <!doctype html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= htmlspecialchars($title) ?> · Reverse Proxy Lab</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    </head>
    <body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Hello Proxy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= $active === 'home' ? 'active' : '' ?>" aria-current="page" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active === 'session' ? 'active' : '' ?>" href="session-dashboard.php">Session PHP</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active === 'cookie' ? 'active' : '' ?>" href="cookie-dashboard.php">Cookie</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active === 'transfer' ? 'active' : '' ?>" href="transfer-lab.php">Upload &amp; Download</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active === 'ssl' ? 'active' : '' ?>" href="ssl-check.php">Check HTTPS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active === 'network' ? 'active' : '' ?>" href="network-trace.php">Trace IP</a>
                    </li>
                </ul>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-light btn-sm" href="logout.php">Déconnexion</a>
                </div>
            </div>
        </div>
    </nav>
    <main class="container mb-5">
        <?php if ($status): ?>
            <div class="alert alert-<?= $status['type'] ?> alert-dismissible fade show" role="alert">
                <?= $status['text'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    <?php
}

function render_footer(): void
{
    ?>
    </main>
    <footer class="bg-dark text-white py-3 mt-auto">
        <div class="container text-center small">
            Reverse Proxy Test Lab · PHP <?= htmlspecialchars(PHP_VERSION) ?>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-w72k4zU+0L0BT29IOpc5LD97nDz4NmzPTlZ4H7aBlJYQG2nERFsdQUi1Bj47jKX8" crossorigin="anonymous"></script>
    </body>
    </html>
    <?php
}

function render_test_steps(): void
{
    ?>
    <ol class="list-group list-group-numbered">
        <?php foreach (TEST_STEPS as $step): ?>
            <li class="list-group-item">
                <div class="fw-semibold"><?= htmlspecialchars($step['title']) ?></div>
                <div class="small text-muted"><?= htmlspecialchars($step['description']) ?></div>
            </li>
        <?php endforeach; ?>
    </ol>
    <?php
}
