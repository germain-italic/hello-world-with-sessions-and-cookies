<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$acRequestMethod = $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] ?? '';
$acRequestHeaders = $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ?? '';

render_header('CORS Lab', 'cors');
?>
<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Cross-Origin Resource Sharing</h1>
                <p class="text-muted small">
                    Cette page aide à vérifier la propagation des en-têtes <code>Access-Control-*</code> à travers le reverse proxy.
                    Utilisez-la pour contrôler à la fois les requêtes simples, les pré-vols <code>OPTIONS</code> et la cohérence des origines.
                </p>
                <div class="alert alert-info">
                    <div><strong>Origin détecté :</strong> <code><?= htmlspecialchars($origin ?: 'aucun (même origine)') ?></code></div>
                    <div><strong>Pré-vol demandé :</strong> <code><?= htmlspecialchars($acRequestMethod ?: 'non') ?></code></div>
                    <div><strong>Headers demandés :</strong> <code><?= htmlspecialchars($acRequestHeaders ?: 'non précisé') ?></code></div>
                </div>
                <h2 class="h6 text-uppercase text-muted">Check-list recommandée</h2>
                <ol class="small text-muted">
                    <li>Exécuter un <code>curl</code> avec <code>Origin</code> personnalisé pour vérifier que l'en-tête <code>Access-Control-Allow-Origin</code> est transmis.</li>
                    <li>Effectuer une requête <code>OPTIONS</code> (pré-vol) via le proxy et vérifier la présence de <code>Access-Control-Allow-Methods</code> et <code>Access-Control-Allow-Headers</code>.</li>
                    <li>Confirmer qu'en l'absence de configuration, la réponse ne contient pas d'en-têtes CORS (comportement strict).</li>
                    <li>Tester une réponse "open" (<code>*</code>) puis une réponse "credentials" (qui reflète l'origine et autorise les cookies) pour observer la différence.</li>
                    <li>Contrôler que le proxy ne duplique pas ni ne supprime les en-têtes, et qu'une seule origine est retournée.</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5">Tests interactifs</h2>
                <p class="small text-muted">Déclenchez ci-dessous des requêtes vers <code>cors-test.php</code>. Les entêtes affichés correspondent à la réponse serveur, utiles pour valider la configuration.</p>
                <div class="d-grid gap-2 mb-3">
                    <button class="btn btn-outline-primary" data-cors-policy="open">Policy "open" (Allow-Origin: *)</button>
                    <button class="btn btn-outline-primary" data-cors-policy="mirror">Policy "mirror" (reflète Origin)</button>
                    <button class="btn btn-outline-primary" data-cors-policy="credentials">Policy "credentials" (Allow-Credentials)</button>
                    <button class="btn btn-outline-danger" data-cors-policy="strict">Policy "strict" (aucun en-tête)</button>
                </div>
                <pre id="cors-result" class="bg-light p-3 border rounded small mb-0">Cliquez sur un bouton pour lancer un test.</pre>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">Commandes utiles</h2>
                <p class="small text-muted">Adapter <code>https://votre-domaine</code> selon votre reverse proxy.</p>
                <pre class="bg-light p-3 border rounded small mb-3"><code>curl -i \
  -H "Origin: https://demo-client.example" \
  https://votre-domaine/cors-test.php?policy=open

curl -i -X OPTIONS \
  -H "Origin: https://demo-client.example" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type" \
  https://votre-domaine/cors-test.php?policy=credentials

curl -i \
  -H "Origin: https://demo-client.example" \
  https://votre-domaine/cors-test.php?policy=strict</code></pre>
                <p class="small text-muted mb-0">
                    Attendez-vous à ce que l'accès strict renvoie une erreur CORS côté navigateur, tandis que les policies <code>open</code> et <code>credentials</code> doivent réussir selon votre configuration.
                </p>
            </div>
        </div>
    </div>
</div>
<script>
const corsButtons = document.querySelectorAll('[data-cors-policy]');
const resultEl = document.getElementById('cors-result');

async function runCorsTest(policy) {
    resultEl.textContent = 'Test en cours pour policy="' + policy + '"...';
    try {
        const response = await fetch('cors-test.php?policy=' + encodeURIComponent(policy), {
            method: 'GET',
            credentials: policy === 'credentials' ? 'include' : 'same-origin',
        });

        const payload = await response.json();
        const headers = {};
        response.headers.forEach((value, key) => {
            headers[key] = value;
        });

        resultEl.textContent = JSON.stringify({
            status: response.status,
            statusText: response.statusText,
            headers,
            body: payload,
        }, null, 2);
    } catch (error) {
        resultEl.textContent = 'Erreur durant le test CORS : ' + error;
    }
}

corsButtons.forEach((btn) => {
    btn.addEventListener('click', () => runCorsTest(btn.dataset.corsPolicy));
});
</script>
<?php
render_footer();
