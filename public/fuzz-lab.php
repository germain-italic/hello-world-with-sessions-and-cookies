<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$vector = $_GET['vector'] ?? '';
$pathInfo = $_SERVER['REQUEST_URI'] ?? '';
$queryString = $_SERVER['QUERY_STRING'] ?? '';
$decodedVector = $vector !== '' ? rawurldecode($vector) : '';

$vectorSamples = [
    '../etc/passwd',
    '..%2f..%2f..%2fetc%2fpasswd',
    '%2e%2e/%2e%2e/%2e%2e/etc/passwd',
    '%2fapi%2f%2e%2e%2f',
    '%25%32%66',
    'a%00b',
    '///multiple////slashes',
    '%2e%2e%5cwindows%5csystem32',
];

render_header('Robustesse & Fuzz', 'fuzz');
?>
<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Tests de robustesse des URLs</h1>
                <p class="text-muted small">
                    Envoyez des chemins et paramètres encodés pour vérifier comment le reverse proxy les transmet.
                    L’objectif est de détecter les normalisations excessives, les doubles décodages ou les filtrages non désirés.
                </p>
                <form class="row g-3 mb-4" method="get">
                    <div class="col-12 col-md-9">
                        <label for="vector" class="form-label">Vecteur encodé</label>
                        <input type="text" class="form-control" id="vector" name="vector" value="<?= htmlspecialchars($vector) ?>" placeholder="%2e%2e/%2e%2e/%2e%2e/etc/passwd">
                    </div>
                    <div class="col-12 col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Tester</button>
                    </div>
                </form>
                <div class="alert alert-info">
                    <div><strong>REQUEST_URI :</strong> <code><?= htmlspecialchars($pathInfo) ?></code></div>
                    <div><strong>Query string :</strong> <code><?= htmlspecialchars($queryString ?: 'vide') ?></code></div>
                    <?php if ($vector !== ''): ?>
                        <div><strong>Décodage brut :</strong> <code><?= htmlspecialchars($decodedVector) ?></code></div>
                    <?php endif; ?>
                </div>
                <h2 class="h6 text-uppercase text-muted">Points de contrôle</h2>
                <ol class="small text-muted">
                    <li>Comparer la valeur encodée et décodée pour détecter un double décodage.</li>
                    <li>Observer les logs du backend : le proxy supprime-t-il les segments suspects (<code>../</code> etc.) ?</li>
                    <li>Tester les caractères nuls (<code>%00</code>) ou les séquences Windows (<code>%5c</code>) pour voir si le proxy les transforme.</li>
                    <li>Valider que les slashes multiples ne sont pas effacés si l'application en a besoin.</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5">Vecteurs préremplis</h2>
                <div class="list-group mb-3">
                    <?php foreach ($vectorSamples as $sample): ?>
                        <a class="list-group-item list-group-item-action" href="fuzz-lab.php?vector=<?= htmlspecialchars($sample) ?>">
                            <code><?= htmlspecialchars($sample) ?></code>
                        </a>
                    <?php endforeach; ?>
                </div>
                <h2 class="h6 text-uppercase text-muted">En-têtes utiles</h2>
                <dl class="row small mb-0">
                    <dt class="col-sm-5">Host</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'non fourni') ?></code></dd>
                    <dt class="col-sm-5">X-Forwarded-For</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'non fourni') ?></code></dd>
                    <dt class="col-sm-5">User-Agent</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'non fourni') ?></code></dd>
                </dl>
                <p class="small text-muted mt-3 mb-0">Astuce : dépilez simultanément les logs du proxy et du backend pour repérer les divergences.</p>
            </div>
        </div>
    </div>
</div>
<?php
render_footer();
