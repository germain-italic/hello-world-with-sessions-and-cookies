<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
$contentEncoding = $_SERVER['HTTP_CONTENT_ENCODING'] ?? '';
$transferEncoding = $_SERVER['HTTP_TRANSFER_ENCODING'] ?? '';
$via = $_SERVER['HTTP_VIA'] ?? '';
$forwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
$forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';

$serverEncoding = $_SERVER['HTTP_X_CONTENT_ENCODING'] ?? '';
$proxyEncoding = $_SERVER['HTTP_X_PROXY_CONTENT_ENCODING'] ?? '';

$gzipNegotiated = stripos($acceptEncoding, 'gzip') !== false;
$brotliNegotiated = stripos($acceptEncoding, 'br') !== false;

// Try to infer who applied gzip by using custom headers if provided.
$appliedBy = [];
if ($contentEncoding) {
    $appliedBy[] = 'Serveur final (Content-Encoding: ' . $contentEncoding . ')';
}
if ($serverEncoding) {
    $appliedBy[] = 'Serveur (X-Content-Encoding: ' . $serverEncoding . ')';
}
if ($proxyEncoding) {
    $appliedBy[] = 'Reverse proxy (X-Proxy-Content-Encoding: ' . $proxyEncoding . ')';
}

render_header('Compression GZIP', 'gzip');
?>
<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Diagnostic compression</h1>
                <p class="text-muted small">
                    Utilisez cette page pour vérifier la négociation et l'application de la compression (gzip, brotli) entre le client, le reverse proxy et l'hôte Apache.
                </p>
                <div class="alert alert-info">
                    <div><strong>Accept-Encoding :</strong> <code><?= htmlspecialchars($acceptEncoding ?: 'non fourni') ?></code></div>
                    <div><strong>Content-Encoding :</strong> <code><?= htmlspecialchars($contentEncoding ?: 'non fourni') ?></code></div>
                </div>
                <h2 class="h6 text-uppercase text-muted">Points de contrôle recommandés</h2>
                <ol class="small text-muted">
                    <li>Depuis le client, vérifier que <code>Accept-Encoding</code> inclut <code>gzip</code> et/ou <code>br</code>.</li>
                    <li>Via le proxy, observer si <code>Content-Encoding</code> est défini. Si oui, identifier l'émetteur (proxy ou host).</li>
                    <li>S'assurer que seule une couche applique la compression (éviter double gzip). Les headers <code>Via</code> ou custom peuvent servir à différencier.</li>
                    <li>Valider que la réponse compressée est servie en HTTP 200 et que le reverse proxy ne supprime pas l'encodage.</li>
                    <li>Comparer la taille (Content-Length ou transfert chunked) entre version compressée et non compressée.</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5">En-têtes observés</h2>
                <dl class="row small mb-0">
                    <dt class="col-sm-5">Content-Encoding</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($contentEncoding ?: 'non fourni') ?></code></dd>

                    <dt class="col-sm-5">X-Content-Encoding</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($serverEncoding ?: 'non fourni') ?></code></dd>

                    <dt class="col-sm-5">X-Proxy-Content-Encoding</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($proxyEncoding ?: 'non fourni') ?></code></dd>

                    <dt class="col-sm-5">Transfer-Encoding</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($transferEncoding ?: 'non fourni') ?></code></dd>

                    <dt class="col-sm-5">Via</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($via ?: 'non fourni') ?></code></dd>

                    <dt class="col-sm-5">X-Forwarded-For</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($forwardedFor ?: 'non fourni') ?></code></dd>

                    <dt class="col-sm-5">X-Forwarded-Proto</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($forwardedProto ?: 'non fourni') ?></code></dd>
                </dl>
                <hr>
                <h3 class="h6">Analyse rapide</h3>
                <ul class="small text-muted ps-3">
                    <li>Négociation gzip côté client : <?= $gzipNegotiated ? '<span class="text-success">oui</span>' : '<span class="text-danger">non</span>' ?></li>
                    <li>Négociation brotli côté client : <?= $brotliNegotiated ? '<span class="text-success">oui</span>' : '<span class="text-warning">non</span>' ?></li>
                    <li>Compression appliquée par :
                        <ul class="ps-3">
                            <?php if ($appliedBy): ?>
                                <?php foreach ($appliedBy as $info): ?>
                                    <li><?= htmlspecialchars($info) ?></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li>Aucune source identifiée (probablement pas de compression).</li>
                            <?php endif; ?>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php
render_footer();
