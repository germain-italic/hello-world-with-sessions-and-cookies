<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

require_trusted_proxy_access();

$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? 'inconnu';
$forwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'non défini';
$host = $_SERVER['HTTP_HOST'] ?? 'non défini';
$via = $_SERVER['HTTP_VIA'] ?? 'non défini';
$forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'non défini';
$forwardedPort = $_SERVER['HTTP_X_FORWARDED_PORT'] ?? 'non défini';

render_header('Proxy Only Lab', 'proxy');
?>
<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Accès réservé au reverse proxy</h1>
                <p class="text-muted small">
                    Si vous voyez cette page via l'URL réelle du serveur Apache, la configuration n'est pas correcte.
                    Elle doit être accessible uniquement lorsqu'une requête transite par le reverse proxy déclaré.
                </p>
                <div class="alert alert-success">
                    Accès autorisé via le proxy <strong><?= htmlspecialchars($remoteAddr) ?></strong>.
                </div>
                <h2 class="h6 text-uppercase text-muted">Check-list proposée</h2>
                <ol class="small text-muted">
                    <li>Depuis le reverse proxy, appeler cette page et vérifier qu'elle s'affiche (statut HTTP 200).</li>
                    <li>Depuis l'hôte Apache direct (bypass proxy), la page doit répondre 403 Forbidden.</li>
                    <li>S'assurer que l'IP du proxy correspond à la configuration (<code><?= htmlspecialchars(implode(', ', TRUSTED_PROXY_IPS)) ?></code>).</li>
                    <li>Contrôler les en-têtes <code>Via</code> et <code>X-Forwarded-*</code> pour documenter le chemin de requête.</li>
                </ol>
                <div class="alert alert-info mt-4">
                    <strong>Configurer les IP autorisées :</strong>
                    <ol class="small text-muted mb-0">
                        <li>Copier le fichier <code>.env.dist</code> vers <code>.env</code> à la racine du projet.</li>
                        <li>Éditer <code>.env</code> et renseigner <code>TRUSTED_PROXY_IPS</code> (liste d'IP séparées par des virgules), par exemple&nbsp;:<br>
                            <code>TRUSTED_PROXY_IPS=51.75.251.128,203.0.113.10</code></li>
                        <li>Redémarrer Apache/PHP-FPM si nécessaire pour recharger la configuration.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5">En-têtes observés</h2>
                <dl class="row small mb-0">
                    <dt class="col-sm-5">REMOTE_ADDR</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($remoteAddr) ?></code></dd>

                    <dt class="col-sm-5">X-Forwarded-For</dt>
                    <dd class="col-sm-7">
                        <textarea class="form-control form-control-sm" rows="2" readonly><?= htmlspecialchars($forwardedFor) ?></textarea>
                    </dd>

                    <dt class="col-sm-5">X-Forwarded-Proto</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($forwardedProto) ?></code></dd>

                    <dt class="col-sm-5">X-Forwarded-Port</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($forwardedPort) ?></code></dd>

                    <dt class="col-sm-5">Via</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($via) ?></code></dd>

                    <dt class="col-sm-5">Host</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($host) ?></code></dd>
                </dl>
            </div>
        </div>
    </div>
</div>
<?php
render_footer();
