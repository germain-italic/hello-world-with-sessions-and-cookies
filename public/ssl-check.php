<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$serverHttps = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
$serverPort = (int) ($_SERVER['SERVER_PORT'] ?? 0);
$forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
$forwardedSsl = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_SSL'] ?? ''));
$forwardedPort = (string) ($_SERVER['HTTP_X_FORWARDED_PORT'] ?? '');
$scheme = strtolower((string) ($_SERVER['REQUEST_SCHEME'] ?? ($serverHttps ? 'https' : 'http')));

$isHttps = $serverHttps || $forwardedProto === 'https' || $forwardedSsl === 'on' || $scheme === 'https' || $serverPort === 443 || $forwardedPort === '443';

$checks = [
    [
        'label' => 'Serveur local détecté en HTTPS',
        'status' => $serverHttps ? 'success' : 'danger',
        'detail' => $serverHttps ? '$_SERVER["HTTPS"] est actif.' : '$_SERVER["HTTPS"] est absent ou à "off".',
    ],
    [
        'label' => 'En-tête X-Forwarded-Proto présent',
        'status' => $forwardedProto === 'https' ? 'success' : ($forwardedProto === '' ? 'warning' : 'danger'),
        'detail' => $forwardedProto === '' ? 'X-Forwarded-Proto non transmis (vérifier la configuration nginx).' : 'X-Forwarded-Proto = ' . htmlspecialchars($forwardedProto),
    ],
    [
        'label' => 'En-tête X-Forwarded-SSL ou X-Forwarded-Port cohérent',
        'status' => ($forwardedSsl === 'on' || $forwardedPort === '443') ? 'success' : 'warning',
        'detail' => 'X-Forwarded-SSL = ' . ($forwardedSsl ?: 'n/a') . ' · X-Forwarded-Port = ' . ($forwardedPort ?: 'n/a'),
    ],
    [
        'label' => 'Port utilisé côté serveur',
        'status' => $serverPort === 443 ? 'success' : 'info',
        'detail' => '$_SERVER["SERVER_PORT"] = ' . ($serverPort ?: 'n/a'),
    ],
    [
        'label' => 'Schéma détecté',
        'status' => $scheme === 'https' ? 'success' : 'warning',
        'detail' => 'REQUEST_SCHEME = ' . htmlspecialchars($scheme),
    ],
];

render_header('Vérification HTTPS', 'ssl');
?>
<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Diagnostic HTTPS (dernière requête)</h1>
                <p class="text-muted small">
                    Cette page lit les variables serveur envoyées par Apache et par le reverse proxy.
                    Actualisez-la via le proxy HTTPS pour vérifier que les en-têtes sont correctement injectés.
                </p>
                <div class="alert alert-<?= $isHttps ? 'success' : 'danger' ?>">
                    <?= $isHttps
                        ? 'HTTPS détecté via la pile serveur ou les en-têtes du proxy.'
                        : 'Aucun signal HTTPS détecté. Vérifiez la terminaison TLS côté proxy et la réécriture des en-têtes.' ?>
                </div>
                <div class="list-group">
                    <?php foreach ($checks as $check): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($check['label']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($check['detail']) ?></div>
                            </div>
                            <span class="badge bg-<?= $check['status'] ?> rounded-pill text-uppercase"><?= $check['status'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5">Tests complémentaires recommandés</h2>
                <p class="text-muted small">Exécuter ces commandes depuis une machine cliente :</p>
                <pre class="bg-light p-3 border rounded small mb-3"><code>curl -I https://votre-domaine.example
curl -I http://votre-domaine.example
openssl s_client -connect votre-domaine.example:443 -servername votre-domaine.example</code></pre>
                <ul class="small text-muted ps-3">
                    <li>Vérifier que la redirection HTTP→HTTPS est en place.</li>
                    <li>Confirmer la date d'expiration et la chaîne du certificat.</li>
                    <li>Observer les en-têtes `Strict-Transport-Security`, `Content-Security-Policy` si configurés.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php
render_footer();
