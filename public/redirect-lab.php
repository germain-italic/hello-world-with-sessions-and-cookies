<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$scheme = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ($_SERVER['REQUEST_SCHEME'] ?? ((isset($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') ? 'https' : 'http'));
$host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost');
$baseUrl = $scheme . '://' . $host;

$scenarios = [
    [
        'label' => '301 vers page relative',
        'description' => 'Location relatif, cache long (301).',
        'url' => 'redirect-test.php?code=301&target=/session-dashboard.php',
    ],
    [
        'label' => '302 vers domaine public',
        'description' => 'Redirection temporaire sur la page d’accueil proxifiée.',
        'url' => 'redirect-test.php?code=302&target=' . rawurlencode($baseUrl . '/index.php?via=redirect'),
    ],
    [
        'label' => '303 vers ressource statique',
        'description' => 'Redirection 303 POST → GET.',
        'url' => 'redirect-test.php?code=303&target=/downloads/proxy-test.txt',
    ],
    [
        'label' => '307 vers HTTPS',
        'description' => 'Conserver la méthode (307) et vérifier que le proxy respecte le schéma.',
        'url' => 'redirect-test.php?code=307&target=' . rawurlencode($baseUrl . '/proxy-only.php'),
    ],
    [
        'label' => '308 vers HTTP',
        'description' => 'Redirection permanente vers http://backend (détecter les mismatch).',
        'url' => 'redirect-test.php?code=308&target=' . rawurlencode('http://' . $host . '/session-login.php'),
    ],
];

render_header('Redirection Lab', 'redirects');
?>
<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Tests de redirection</h1>
                <p class="text-muted small">
                    Cette page déclenche différents codes 3xx afin de vérifier que le reverse proxy ne modifie pas les en-têtes <code>Location</code> et respecte les codes de statut.
                </p>
                <div class="alert alert-info">
                    <div><strong>Base URL détectée :</strong> <code><?= htmlspecialchars($baseUrl) ?></code></div>
                </div>
                <h2 class="h6 text-uppercase text-muted">Scénarios</h2>
                <div class="list-group mb-3">
                    <?php foreach ($scenarios as $scenario): ?>
                        <a class="list-group-item list-group-item-action" href="<?= htmlspecialchars($scenario['url']) ?>">
                            <div class="fw-semibold"><?= htmlspecialchars($scenario['label']) ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($scenario['description']) ?></div>
                            <div class="small"><code><?= htmlspecialchars($scenario['url']) ?></code></div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <h2 class="h6 text-uppercase text-muted">Points de contrôle</h2>
                <ol class="small text-muted">
                    <li>Utiliser <code>curl -I -L</code> pour suivre les redirections et vérifier que le proxy ne réécrit pas l'hôte.</li>
                    <li>Comparer les codes 301 vs 302 et s’assurer que les clients respectent la cache.</li>
                    <li>Pour 307/308, envoyer une requête <code>POST</code> et vérifier que la méthode est préservée.</li>
                    <li>Observer si le proxy applique une réécriture HTTP→HTTPS automatique (peut casser les tests).</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5">Commandes utiles</h2>
                <pre class="bg-light p-3 border rounded small mb-3"><code>curl -I https://votre-proxy/redirect-test.php?code=301&amp;target=%2Fsession-dashboard.php
curl -I -L -X POST -d "name=test" https://votre-proxy/redirect-test.php?code=307&amp;target=<?= htmlspecialchars(rawurlencode($baseUrl . '/cookie-dashboard.php')) ?></code></pre>
                <p class="small text-muted mb-0">
                    Comparez également la réponse directe (sans proxy) pour identifier les différences.</p>
            </div>
        </div>
    </div>
</div>
<?php
render_footer();
