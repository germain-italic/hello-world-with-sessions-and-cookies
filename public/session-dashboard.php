<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

require_session_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'set') {
        $key = trim($_POST['key'] ?? '');
        $value = trim($_POST['value'] ?? '');

        if ($key === '') {
            redirect_with_status('session-dashboard.php', 'warning', 'Merci de renseigner une clé pour la variable de session.');
        }

        set_session_custom_value($key, $value);
        redirect_with_status('session-dashboard.php', 'success', 'Variable de session mise à jour.');
    }

    if ($action === 'remove') {
        $key = $_POST['key'] ?? '';
        remove_session_custom_value($key);
        redirect_with_status('session-dashboard.php', 'info', 'Variable de session supprimée.');
    }

    if ($action === 'clear-all') {
        ensure_session_started();
        unset($_SESSION['custom_data']);
        redirect_with_status('session-dashboard.php', 'info', 'Toutes les variables custom ont été supprimées.');
    }
}

$session = get_session_details();
$custom = $session['custom_data'] ?? [];

render_header('Session PHP', 'session');
?>
<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Session PHP</h1>
                <p class="text-muted small">
                    Cette section exploite <code>$_SESSION</code>. Naviguez entre les pages pour vérifier la stabilité via le reverse proxy.
                </p>
                <dl class="row mb-0">
                    <dt class="col-sm-5">Session ID</dt>
                    <dd class="col-sm-7"><code><?= htmlspecialchars($session['session_id']) ?></code></dd>
                    <dt class="col-sm-5">Utilisateur</dt>
                    <dd class="col-sm-7"><?= htmlspecialchars((string) $session['auth_user']) ?></dd>
                    <dt class="col-sm-5">Ouverture</dt>
                    <dd class="col-sm-7">
                        <?= $session['auth_started_at'] ? date('Y-m-d H:i:s', (int) $session['auth_started_at']) : 'n/a' ?>
                        <span class="text-muted d-block small">Fuseau serveur : <?= date_default_timezone_get() ?></span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h5">Manipuler les données de session</h2>
                <form method="post" class="row g-2 mb-3">
                    <input type="hidden" name="action" value="set">
                    <div class="col-12 col-sm-5">
                        <label class="form-label" for="session-key">Clé</label>
                        <input type="text" class="form-control" id="session-key" name="key" placeholder="ex: proxy" required>
                    </div>
                    <div class="col-12 col-sm-5">
                        <label class="form-label" for="session-value">Valeur</label>
                        <input type="text" class="form-control" id="session-value" name="value" placeholder="ex: pass-through" required>
                    </div>
                    <div class="col-12 col-sm-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Enregistrer</button>
                    </div>
                </form>
                <form method="post">
                    <input type="hidden" name="action" value="clear-all">
                    <button class="btn btn-outline-danger btn-sm" type="submit">Vider les variables custom</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-body">
        <h2 class="h5">Variables custom actuellement stockées</h2>
        <?php if (empty($custom)): ?>
            <p class="text-muted">Aucune variable n'a encore été enregistrée.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th>Clé</th>
                        <th>Valeur</th>
                        <th>Mise à jour</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($custom as $key => $data): ?>
                        <tr>
                            <td><code><?= htmlspecialchars((string) $key) ?></code></td>
                            <td><?= htmlspecialchars((string) ($data['value'] ?? '')) ?></td>
                            <td><?= isset($data['updated_at']) ? date('Y-m-d H:i:s', (int) $data['updated_at']) : 'n/a' ?></td>
                            <td class="text-end">
                                <form method="post">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="key" value="<?= htmlspecialchars((string) $key) ?>">
                                    <button class="btn btn-outline-secondary btn-sm" type="submit">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
render_footer();
