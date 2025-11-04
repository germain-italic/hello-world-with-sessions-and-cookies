<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$backendHost = $_SERVER['HTTP_HOST'] ?? 'inconnu';
$forwardedHost = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? '';
$forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ($_SERVER['REQUEST_SCHEME'] ?? 'http');
$scheme = isset($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
$preferredScheme = $forwardedProto ?: $scheme;
$publicHost = $forwardedHost ?: $backendHost;

$absoluteLinks = [
    [
        'label' => 'Lien absolu (backend)',
        'url' => sprintf('%s://%s/session-dashboard.php', $scheme, $backendHost),
    ],
    [
        'label' => 'Lien absolu (proxy)',
        'url' => sprintf('%s://%s/session-dashboard.php', $preferredScheme, $publicHost),
    ],
    [
        'label' => 'Asset absolu (image)',
        'url' => sprintf('%s://%s/assets/img/italic-logo-512x512.png', $preferredScheme, $publicHost),
    ],
    [
        'label' => 'API absolue (CORS test)',
        'url' => sprintf('%s://%s/cors-test.php?policy=open', $preferredScheme, $publicHost),
    ],
];

render_header('Liens absolus', 'absolute');
?>
<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Réécriture des URLs absolues</h1>
                <p class="text-muted small">
                    Les reverse proxies doivent souvent réécrire les URLs absolues générées par l'application pour refléter le domaine public.
                    Utilisez cette page pour vérifier qu'aucun lien ne pointe vers l'hôte interne (<code><?= htmlspecialchars($backendHost) ?></code>).
                </p>
                <div class="alert alert-info">
                    <div><strong>Host backend :</strong> <code><?= htmlspecialchars($backendHost) ?></code></div>
                    <div><strong>Host perçu via proxy :</strong> <code><?= htmlspecialchars($publicHost) ?></code></div>
                    <div><strong>Schéma détecté :</strong> <code><?= htmlspecialchars($preferredScheme) ?></code></div>
                </div>
                <h2 class="h6 text-uppercase text-muted">Points de contrôle</h2>
                <ol class="small text-muted">
                    <li>Via DevTools, inspecter les liens/ressources générés et confirmer qu'ils utilisent le domaine public.</li>
                    <li>Vérifier que le proxy réécrit <code>Location</code> et les balises <code>&lt;base&gt;</code> si l'application les définit.</li>
                    <li>S'assurer que les ressources statiques (CSS/JS/images) sont servies via le proxy et non directement depuis le backend.</li>
                    <li>Contrôler que les URLs absolues HTTP sont ré-écrites en HTTPS si nécessaire (redirection 301/302 ou réécriture de contenu).</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5">Liens générés</h2>
                <div class="list-group">
                    <?php foreach ($absoluteLinks as $link): ?>
                        <a class="list-group-item list-group-item-action" href="<?= htmlspecialchars($link['url']) ?>" target="_blank" rel="noopener">
                            <div class="fw-semibold"><?= htmlspecialchars($link['label']) ?></div>
                            <div class="small text-muted"><code><?= htmlspecialchars($link['url']) ?></code></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">Exemple d'intégration</h2>
                <p class="small text-muted">Ce bloc affiche une image absolue générée avec l'hôte public.</p>
                <img src="<?= htmlspecialchars($absoluteLinks[2]['url']) ?>" alt="Logo Italic" class="img-fluid rounded border">
                <p class="small text-muted mt-2 mb-0">
                    Si l'image ne s'affiche pas ou pointe vers un domaine interne, ajuster la configuration du proxy (<code>sub_filter</code>, <code>proxy_cookie_domain</code>, etc.).
                </p>
            </div>
        </div>
    </div>
</div>
<?php
render_footer();
