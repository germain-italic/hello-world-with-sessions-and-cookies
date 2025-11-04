<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

require_cookie_auth();

$payload = get_cookie_payload();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'refresh') {
        refresh_auth_cookie($payload);
        redirect_with_status('cookie-dashboard.php', 'success', 'Cookie rafraîchi (nouvelle date et TTL).');
    }

    if ($action === 'update-user') {
        $newUser = trim($_POST['user'] ?? '');
        if ($newUser === '') {
            redirect_with_status('cookie-dashboard.php', 'warning', 'Merci de préciser un utilisateur.');
        }

        $payload['user'] = $newUser;
        refresh_auth_cookie($payload);
        redirect_with_status('cookie-dashboard.php', 'info', 'Nom d\'utilisateur mis à jour dans le cookie.');
    }

    if ($action === 'add-field') {
        $field = trim($_POST['field'] ?? '');
        $value = trim($_POST['value'] ?? '');

        if ($field === '') {
            redirect_with_status('cookie-dashboard.php', 'warning', 'Merci de préciser une clé pour le cookie.');
        }

        $payload[$field] = $value;
        refresh_auth_cookie($payload);
        redirect_with_status('cookie-dashboard.php', 'success', 'Champ ajouté ou mis à jour dans le cookie.');
    }

    if ($action === 'clear') {
        clear_auth_cookie();
        redirect_with_status('cookie-login.php', 'info', 'Cookie supprimé. Relancez un test depuis le parcours cookie.');
    }
}

$rawCookie = $_COOKIE[COOKIE_AUTH_NAME] ?? '';
$decoded = $payload;

render_header('Cookie Lab', 'cookie');
?>
<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h1 class="h4 mb-3">Cookie d'authentification</h1>
                <p class="text-muted small">
                    Le cookie est encodé en base64 et contient un JSON. Toute modification manuelle doit rester conforme au JSON pour être acceptée.
                </p>
                <dl class="row mb-0">
                    <dt class="col-sm-5">Nom du cookie</dt>
                    <dd class="col-sm-7"><code><?= COOKIE_AUTH_NAME ?></code></dd>
                    <dt class="col-sm-5">Valeur brute</dt>
                    <dd class="col-sm-7">
                        <textarea class="form-control form-control-sm" rows="3" readonly><?= htmlspecialchars($rawCookie) ?></textarea>
                    </dd>
                    <dt class="col-sm-5">Utilisateur</dt>
                    <dd class="col-sm-7"><?= htmlspecialchars((string) ($decoded['user'] ?? '')) ?></dd>
                    <dt class="col-sm-5">Émis à</dt>
                    <dd class="col-sm-7">
                        <?= isset($decoded['issued_at']) ? date('Y-m-d H:i:s', (int) $decoded['issued_at']) : 'n/a' ?>
                    </dd>
                    <?php if (isset($decoded['refreshed_at'])): ?>
                        <dt class="col-sm-5">Rafraîchi à</dt>
                        <dd class="col-sm-7"><?= date('Y-m-d H:i:s', (int) $decoded['refreshed_at']) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5">Actions rapides</h2>
                <div class="d-grid gap-2">
                    <form method="post">
                        <input type="hidden" name="action" value="refresh">
                        <button class="btn btn-primary" type="submit">Rafraîchir le cookie (TTL + timestamp)</button>
                    </form>
                    <form method="post" class="row g-2">
                        <input type="hidden" name="action" value="update-user">
                        <div class="col-8">
                            <label class="form-label" for="user">Utilisateur</label>
                            <input type="text" class="form-control" id="user" name="user" value="<?= htmlspecialchars((string) ($decoded['user'] ?? '')) ?>" required>
                        </div>
                        <div class="col-4 d-flex align-items-end">
                            <button class="btn btn-outline-primary w-100" type="submit">Mettre à jour</button>
                        </div>
                    </form>
                    <form method="post" class="row g-2">
                        <input type="hidden" name="action" value="add-field">
                        <div class="col-5">
                            <label class="form-label" for="field">Clé</label>
                            <input type="text" class="form-control" id="field" name="field" placeholder="ex: site" required>
                        </div>
                        <div class="col-5">
                            <label class="form-label" for="value">Valeur</label>
                            <input type="text" class="form-control" id="value" name="value" placeholder="ex: proxy" required>
                        </div>
                        <div class="col-2 d-flex align-items-end">
                            <button class="btn btn-outline-success w-100" type="submit">OK</button>
                        </div>
                    </form>
                    <form method="post">
                        <input type="hidden" name="action" value="clear">
                        <button class="btn btn-outline-danger" type="submit">Supprimer le cookie</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-body">
        <h2 class="h5">Payload complet (JSON)</h2>
        <pre class="bg-light p-3 border rounded small mb-0"><?= htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) ?></pre>
    </div>
</div>
<?php
render_footer();
