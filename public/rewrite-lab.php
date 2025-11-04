<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$scenario = $_GET['scenario'] ?? 'default';
$validScenarios = ['hello', 'status', 'download', 'proxy'];

if (!in_array($scenario, $validScenarios, true)) {
    $scenario = 'default';
}

$messages = [
    'default' => 'Aucun slug détecté. Utilisez les URLs réécrites proposées pour tester.',
    'hello' => 'Slug "hello" capturé via une règle RewriteRule.',
    'status' => 'Règle statique déclenchée (rewrite/status) → rewrite-lab.php?scenario=status.',
    'download' => 'Réécriture vers download.php?file=proxy-test.txt. Vérifiez la réponse du fichier.',
    'proxy' => 'Slug "proxy" capturé. Validez que la chaîne de réécriture conserve les paramètres.',
];

$status = $messages[$scenario] ?? $messages['default'];

render_header('Rewrite Lab', 'rewrite');
?>
<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Tests d'URL Rewriting</h1>
                <p class="text-muted small">
                    Ces scénarios reposent sur le fichier <code>.htaccess</code> fourni. Les URL « propres » ci-dessous doivent atteindre cette page
                    ou déclencher un téléchargement spécifique. Utilisez le proxy pour confirmer que les règles sont bien prises en compte.
                </p>
                <div class="alert alert-info mb-4">
                    <strong>Résultat courant :</strong> <?= htmlspecialchars($status) ?><br>
                    <span class="small text-muted">REQUEST_URI = <code><?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?></code></span>
                    <span class="small text-muted d-block">QUERY_STRING = <code><?= htmlspecialchars($_SERVER['QUERY_STRING'] ?? '') ?></code></span>
                </div>
                <h2 class="h6 text-uppercase text-muted">Variables reçues</h2>
                <div class="table-responsive mb-4">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>Clé</th>
                            <th>Valeur</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($_GET as $key => $value): ?>
                            <tr>
                                <td><code><?= htmlspecialchars((string) $key) ?></code></td>
                                <td><code><?= htmlspecialchars((string) $value) ?></code></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($_GET)): ?>
                            <tr>
                                <td colspan="2" class="text-muted">Aucun paramètre GET.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <h2 class="h6 text-uppercase text-muted">Scénarios</h2>
                <div class="list-group">
                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="rewrite/test/hello">
                        <span><code>/rewrite/test/hello</code></span>
                        <span class="badge bg-primary">RewriteRule → scenario=hello</span>
                    </a>
                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="rewrite/test/proxy">
                        <span><code>/rewrite/test/proxy</code></span>
                        <span class="badge bg-primary">RewriteRule → scenario=proxy</span>
                    </a>
                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="rewrite/status">
                        <span><code>/rewrite/status</code></span>
                        <span class="badge bg-secondary">RewriteRule statique</span>
                    </a>
                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="rewrite/download">
                        <span><code>/rewrite/download</code></span>
                        <span class="badge bg-success">Rewrite → download.php</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5">Instructions rapides</h2>
                <ol class="small text-muted">
                    <li>Activer <code>AllowOverride All</code> dans Apache pour que <code>.htaccess</code> soit pris en compte.</li>
                    <li>Redéployer derrière le reverse proxy et s'assurer qu'il ne casse pas les URL réécrites (pas de double slash, pas de rewrite côté nginx).</li>
                    <li>Surveiller les logs Apache et nginx pour vérifier la cible réelle de chaque URL.</li>
                </ol>
                <p class="small text-muted mb-0">
                    Si une URL réécrite renvoie une 404, cela indique que la règle n'est pas appliquée (ou que le proxy modifie la requête). Comparez la valeur
                    de <code>REQUEST_URI</code> ci-dessus avec vos attentes.
                </p>
            </div>
        </div>
    </div>
</div>
<?php
render_footer();
