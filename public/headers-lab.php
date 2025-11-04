<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$securityHeaders = [
    'Strict-Transport-Security' => 'max-age=63072000; includeSubDomains; preload',
    'X-Frame-Options' => 'SAMEORIGIN',
    'X-Content-Type-Options' => 'nosniff',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
    'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
    'Content-Security-Policy' => "default-src 'self'; img-src 'self' data:; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';",
];

foreach ($securityHeaders as $name => $value) {
    header($name . ': ' . $value, true);
}

$etag = '"hl-' . substr(sha1(__FILE__), 0, 12) . '"';
header('ETag: ' . $etag, true);
$lastModified = gmdate('D, d M Y H:i:s', filemtime(__FILE__)) . ' GMT';
header('Last-Modified: ' . $lastModified, true);
$cacheControl = 'public, max-age=120, stale-while-revalidate=60, stale-if-error=300';
header('Cache-Control: ' . $cacheControl, true);
header('Pragma: public', true);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 120) . ' GMT', true);

$responseHeaders = [];
foreach (headers_list() as $header) {
    [$name, $value] = array_map('trim', explode(':', $header, 2) + ['', '']);
    $responseHeaders[$name] = $value;
}

$serverProtocol = $_SERVER['SERVER_PROTOCOL'] ?? 'unknown';
$connection = $_SERVER['HTTP_CONNECTION'] ?? 'non fourni';
$keepAlive = $_SERVER['HTTP_KEEP_ALIVE'] ?? 'non fourni';
$transferEncoding = $_SERVER['HTTP_TRANSFER_ENCODING'] ?? 'non fourni';
$acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? 'non fourni';
$host = $_SERVER['HTTP_HOST'] ?? 'non fourni';
$forwardedHost = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? 'non fourni';

render_header('Headers & Cache Lab', 'headers');
?>
<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Diagnostic des en-têtes</h1>
                <p class="text-muted small">
                    Cette page émet un ensemble d'en-têtes typiques (sécurité, cache, ETag/Last-Modified).
                    Contrôlez via le reverse proxy qu'ils sont conservés, non dupliqués, et qu'aucune politique contradictoire n'est injectée.
                </p>
                <div class="alert alert-info">
                    <div><strong>ETag attendu :</strong> <code><?= htmlspecialchars($etag) ?></code></div>
                    <div><strong>Last-Modified attendu :</strong> <code><?= htmlspecialchars($lastModified) ?></code></div>
                    <div><strong>Cache-Control :</strong> <code><?= htmlspecialchars($cacheControl) ?></code></div>
                </div>
                <h2 class="h6 text-uppercase text-muted">Points de contrôle recommandés</h2>
                <ol class="small text-muted">
                    <li>Lancer <code>curl -I</code> sur l'URL proxifiée et vérifier la présence de chaque en-tête listé ci-dessous.</li>
                    <li>Confirmer que le proxy ne rajoute pas de directives contradictoires (double <code>Cache-Control</code>, <code>Content-Security-Policy</code> réécrite, etc.).</li>
                    <li>Vérifier que <code>ETag</code> et <code>Last-Modified</code> se propagent et que les requêtes conditionnelles (<code>If-None-Match</code>) donnent bien un 304.</li>
                    <li>Observer <code>SERVER_PROTOCOL</code> pour s'assurer que le client conserve HTTP/2 si souhaité.</li>
                    <li>Valider que <code>Connection</code>/<code>Keep-Alive</code> restent cohérents (pas de clôture forcée).</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5">En-têtes de réponse émis</h2>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Valeur</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($responseHeaders as $name => $value): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($name) ?></code></td>
                                <td><code><?= htmlspecialchars($value) ?></code></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">Contexte de la requête</h2>
                <dl class="row small mb-0">
                    <dt class="col-sm-5">SERVER_PROTOCOL</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($serverProtocol) ?></code></dd>
                    <dt class="col-sm-5">Connection</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($connection) ?></code></dd>
                    <dt class="col-sm-5">Keep-Alive</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($keepAlive) ?></code></dd>
                    <dt class="col-sm-5">Transfer-Encoding</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($transferEncoding) ?></code></dd>
                    <dt class="col-sm-5">Accept-Encoding</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($acceptEncoding) ?></code></dd>
                    <dt class="col-sm-5">Host vu par backend</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($host) ?></code></dd>
                    <dt class="col-sm-5">X-Forwarded-Host</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($forwardedHost) ?></code></dd>
                </dl>
            </div>
        </div>
    </div>
</div>
<?php
render_footer();
