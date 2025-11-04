<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$providedUser = $_SERVER['PHP_AUTH_USER'] ?? null;
$providedPassword = $_SERVER['PHP_AUTH_PW'] ?? null;

if ($providedUser !== TEST_USER || $providedPassword !== TEST_PASSWORD) {
    header('WWW-Authenticate: Basic realm="Reverse Proxy Lab"');
    http_response_code(401);
    ?>
    <!doctype html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>Authentification requise</title>
        <style>body{font-family:Arial,sans-serif;background:#f8f9fa;margin:0;padding:3rem;color:#333;} .card{max-width:520px;margin:0 auto;background:#fff;border-radius:8px;padding:2rem;box-shadow:0 15px 35px rgba(0,0,0,0.08);} h1{font-size:1.4rem;margin-bottom:1rem;} code{background:rgba(0,0,0,0.05);padding:0.15rem 0.35rem;border-radius:4px;}</style>
    </head>
    <body>
    <div class="card">
        <h1>Authentification HTTP requise</h1>
        <p>Le serveur attend une authentification BASIC transmise via le reverse proxy.</p>
        <p>Utilisez les identifiants de test : <code><?= htmlspecialchars(TEST_USER) ?></code> / <code><?= htmlspecialchars(TEST_PASSWORD) ?></code>.</p>
        <p class="small">Astuce : <code>curl -u <?= htmlspecialchars(TEST_USER) ?>:<?= htmlspecialchars(TEST_PASSWORD) ?> https://votre-proxy/basic-auth-lab.php</code></p>
    </div>
    </body>
    </html>
    <?php
    exit;
}

$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
$forwardedAuth = $_SERVER['HTTP_PROXY_AUTHORIZATION'] ?? null;

render_header('Basic Auth Lab', 'basic-auth');
?>
<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Authentification BASIC</h1>
                <p class="text-muted small">
                    Votre navigateur (ou votre client HTTP) a transmis un en-tête <code>Authorization</code> jusqu'au backend.
                    Vérifiez que le reverse proxy ne filtre pas les identifiants et qu'il ne réécrit pas l'en-tête.
                </p>
                <div class="alert alert-success">
                    Authentification réussie avec l'utilisateur <strong><?= htmlspecialchars($providedUser ?? '') ?></strong>.
                </div>
                <h2 class="h6 text-uppercase text-muted">Points de contrôle</h2>
                <ol class="small text-muted">
                    <li>Tester <code>curl -I</code> sans credentials pour obtenir un 401, puis avec <code>-u user:pass</code> pour obtenir un 200.</li>
                    <li>Observer si le proxy rajoute son propre en-tête <code>Proxy-Authorization</code> ou supprime <code>Authorization</code>.</li>
                    <li>Valider que l'en-tête <code>WWW-Authenticate</code> remonte bien côté client lors du 401.</li>
                    <li>Confirmer que les flux HTTPS et HTTP renvoient le même comportement (pas de stripping côté proxy SSL).</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5">En-têtes reçus</h2>
                <dl class="row small mb-0">
                    <dt class="col-sm-5">Authorization</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($authHeader ?? 'non reçu') ?></code></dd>
                    <dt class="col-sm-5">Proxy-Authorization</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($forwardedAuth ?? 'non reçu') ?></code></dd>
                    <dt class="col-sm-5">Utilisateur</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($providedUser ?? '') ?></code></dd>
                </dl>
                <hr>
                <pre class="bg-light p-3 border rounded small mb-0"><code>curl -i -u <?= htmlspecialchars(TEST_USER) ?>:<?= htmlspecialchars(TEST_PASSWORD) ?> https://votre-proxy/basic-auth-lab.php
curl -i -H "Authorization: Basic <?= base64_encode(TEST_USER . ':' . TEST_PASSWORD) ?>" https://votre-proxy/basic-auth-lab.php</code></pre>
            </div>
        </div>
    </div>
</div>
<?php
render_footer();
