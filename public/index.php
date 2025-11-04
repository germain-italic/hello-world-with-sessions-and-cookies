<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$sessionDetails = get_session_details();
$sessionActive = is_session_authenticated();
$sessionUser = $sessionDetails['auth_user'] ?? null;

$cookiePayload = get_cookie_payload();
$cookieActive = has_cookie_auth();
$cookieUser = $cookiePayload['user'] ?? null;

$serverHttps = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
$forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
$forwardedSsl = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_SSL'] ?? ''));
$forwardedPort = (string) ($_SERVER['HTTP_X_FORWARDED_PORT'] ?? '');
$serverPort = (int) ($_SERVER['SERVER_PORT'] ?? 0);
$httpsDetected = $serverHttps || $forwardedProto === 'https' || $forwardedSsl === 'on' || $forwardedPort === '443' || $serverPort === 443;
$httpsSignals = array_filter([
    $serverHttps ? 'HTTPS serveur' : '',
    $forwardedProto !== '' ? 'XFP=' . $forwardedProto : '',
    $forwardedSsl !== '' ? 'XF-SSL=' . $forwardedSsl : '',
    $forwardedPort !== '' ? 'XF-Port=' . $forwardedPort : '',
]);

$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? 'inconnu';
$forwardedFor = trim((string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''));
$forwardedIps = $forwardedFor !== '' ? array_filter(array_map('trim', explode(',', $forwardedFor))) : [];
$clientIp = $forwardedIps[0] ?? $remoteAddr;
$proxyIp = $remoteAddr;
$viaTrustedProxy = is_trusted_proxy_request();

$statusSummaries = [
    [
        'title' => 'Session PHP',
        'state' => $sessionActive
            ? 'Active (utilisateur ' . ($sessionUser ?: '?') . ')'
            : 'Inactive',
        'badge_variant' => $sessionActive ? 'success' : 'secondary',
        'badge_text' => $sessionActive ? 'active' : 'off',
        'link' => 'session-dashboard.php',
        'link_label' => 'Ouvrir le parcours session',
    ],
    [
        'title' => 'Session cookie',
        'state' => $cookieActive
            ? 'Cookie présent (utilisateur ' . (($cookieUser ?? '') ?: '?') . ')'
            : 'Cookie absent',
        'badge_variant' => $cookieActive ? 'success' : 'secondary',
        'badge_text' => $cookieActive ? 'présent' : 'absent',
        'link' => 'cookie-dashboard.php',
        'link_label' => 'Ouvrir le parcours cookie',
    ],
    [
        'title' => 'HTTPS',
        'state' => $httpsDetected
            ? 'HTTPS détecté' . (!empty($httpsSignals) ? ' (' . implode(' · ', $httpsSignals) . ')' : '')
            : 'HTTP seulement' . ($forwardedProto ? ' · XFP=' . $forwardedProto : ''),
        'badge_variant' => $httpsDetected ? 'success' : 'danger',
        'badge_text' => $httpsDetected ? 'https' : 'http',
        'link' => 'ssl-check.php',
        'link_label' => 'Diagnostiquer HTTPS',
    ],
    [
        'title' => 'Trace IP',
        'state' => 'Client: ' . $clientIp . ' · Proxy: ' . $proxyIp . ($forwardedIps ? ' · Chaîne XFF (' . count($forwardedIps) . ')' : ''),
        'badge_variant' => $forwardedIps ? 'info' : 'secondary',
        'badge_text' => $forwardedIps ? 'proxifié' : 'direct',
        'link' => 'network-trace.php',
        'link_label' => 'Inspecter les en-têtes IP',
    ],
    [
        'title' => 'Proxy-only',
        'state' => $viaTrustedProxy
            ? 'IP proxy autorisée détectée (' . $proxyIp . ')'
            : 'Accès direct · proxy attendu : ' . (TRUSTED_PROXY_IPS[0] ?? 'config'),
        'badge_variant' => $viaTrustedProxy ? 'success' : 'warning',
        'badge_text' => $viaTrustedProxy ? 'ok' : 'direct',
        'link' => 'proxy-only.php',
        'link_label' => 'Tester la zone proxy-only',
    ],
];

render_header('Accueil', 'home');
?>
<div class="row g-4">
    <div class="col-12 col-lg-7 col-xl-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="card-title h3 mb-3">Hello world · Reverse proxy lab</h1>
                <p class="text-muted mb-4">
                    Ce mini site permet de valider le comportement d'un reverse proxy (nginx) devant une application PHP/Apache.
                    Chaque parcours couvre les points classiques : sessions PHP, cookies personnalisés, transferts de fichiers, rewrite et gestion de déconnexion.
                </p>
                <h2 class="h5">Parcours de test</h2>
                <?php render_test_steps(); ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5 col-xl-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5 mb-3">Identifiants de test</h2>
                <dl class="row mb-0">
                    <dt class="col-sm-5">Login</dt>
                    <dd class="col-sm-7"><code><?= TEST_USER ?></code></dd>
                    <dt class="col-sm-5">Mot de passe</dt>
                    <dd class="col-sm-7"><code><?= TEST_PASSWORD ?></code></dd>
                </dl>
                <p class="small text-muted mt-3 mb-0">
                    Utilisez toujours ces identifiants pour les parcours session et cookie.
                    Les pages dédiées fournissent les formulaires nécessaires.
                </p>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">États rapides</h2>
                <div class="list-group list-group-flush">
                    <?php foreach ($statusSummaries as $summary): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($summary['title']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($summary['state']) ?></div>
                                </div>
                                <span class="badge bg-<?= htmlspecialchars($summary['badge_variant']) ?> text-uppercase"><?= htmlspecialchars($summary['badge_text']) ?></span>
                            </div>
                            <div class="mt-2">
                                <a class="btn btn-sm btn-outline-secondary" href="<?= htmlspecialchars($summary['link']) ?>"><?= htmlspecialchars($summary['link_label']) ?></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
render_footer();
