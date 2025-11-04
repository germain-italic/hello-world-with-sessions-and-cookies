<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

function clean_header(string $value): string
{
    return trim((string) $value);
}

$remoteAddr = clean_header($_SERVER['REMOTE_ADDR'] ?? 'inconnu');
$forwardedFor = clean_header($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
$forwarded = clean_header($_SERVER['HTTP_FORWARDED'] ?? '');
$forwardedHost = clean_header($_SERVER['HTTP_X_FORWARDED_HOST'] ?? '');
$forwardedServer = clean_header($_SERVER['HTTP_X_FORWARDED_SERVER'] ?? '');
$forwardedBy = clean_header($_SERVER['HTTP_X_FORWARDED_BY'] ?? '');
$realIp = clean_header($_SERVER['HTTP_X_REAL_IP'] ?? '');
$userAgent = clean_header($_SERVER['HTTP_USER_AGENT'] ?? 'inconnu');

$ipChain = [];
if ($forwardedFor !== '') {
    $parts = array_map('trim', explode(',', $forwardedFor));
    foreach ($parts as $index => $ip) {
        $ipChain[] = [
            'index' => $index,
            'ip' => $ip,
            'role' => $index === 0 ? 'Client d\'origine (X-Forwarded-For[0])' : 'Proxy intermédiaire #' . $index,
        ];
    }
}

if ($realIp !== '') {
    $ipChain[] = [
        'index' => count($ipChain),
        'ip' => $realIp,
        'role' => 'IP réelle (X-Real-IP)',
    ];
}

$ipChain[] = [
    'index' => count($ipChain),
    'ip' => $remoteAddr,
    'role' => 'IP vue par PHP (REMOTE_ADDR)',
];

render_header('Trace IP & User Agent', 'network');
?>
<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Trace IP</h1>
                <p class="text-muted small">
                    Les reverse proxies ajoutent ou concatènent les en-têtes <code>X-Forwarded-*</code>.
                    Cette page expose la chaîne complète pour comparer l'IP du visiteur, celle du proxy et des nœuds intermédiaires.
                </p>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Adresse IP</th>
                            <th>Rôle</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($ipChain as $entry): ?>
                            <tr>
                                <td><?= (int) $entry['index'] ?></td>
                                <td><code><?= htmlspecialchars($entry['ip']) ?></code></td>
                                <td><?= htmlspecialchars($entry['role']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="small text-muted mb-0">
                    Pour identifier le proxy : comparez l'IP `REMOTE_ADDR` avec la machine où tourne Apache. Les IP supplémentaires dans `X-Forwarded-For` représentent les clients d'origine et les proxies intermédiaires.
                </p>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5">Résumé des en-têtes</h2>
                <dl class="row mb-0 small">
                    <dt class="col-sm-5">REMOTE_ADDR</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($remoteAddr) ?></code></dd>

                    <dt class="col-sm-5">X-Forwarded-For</dt>
                    <dd class="col-sm-7">
                        <textarea class="form-control form-control-sm" rows="2" readonly><?= htmlspecialchars($forwardedFor ?: 'non reçu') ?></textarea>
                    </dd>

                    <dt class="col-sm-5">Forwarded</dt>
                    <dd class="col-sm-7">
                        <textarea class="form-control form-control-sm" rows="2" readonly><?= htmlspecialchars($forwarded ?: 'non reçu') ?></textarea>
                    </dd>

                    <dt class="col-sm-5">X-Forwarded-Host</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($forwardedHost ?: 'non reçu') ?></code></dd>

                    <dt class="col-sm-5">X-Forwarded-Server</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($forwardedServer ?: 'non reçu') ?></code></dd>

                    <dt class="col-sm-5">X-Forwarded-By</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($forwardedBy ?: 'non reçu') ?></code></dd>

                    <dt class="col-sm-5">X-Real-IP</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($realIp ?: 'non reçu') ?></code></dd>
                </dl>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">User Agent</h2>
                <p class="text-muted small">
                    Les proxies peuvent réécrire certains en-têtes. Vérifiez que l'agent utilisateur n'est pas altéré ou tronqué.
                </p>
                <textarea class="form-control" rows="4" readonly><?= htmlspecialchars($userAgent) ?></textarea>
            </div>
        </div>
    </div>
</div>
<?php
render_footer();
