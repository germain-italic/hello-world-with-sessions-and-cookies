<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === TEST_USER && $password === TEST_PASSWORD) {
        authenticate_with_session($username);
        redirect_with_status('session-dashboard.php', 'success', 'Session PHP ouverte avec succès.');
    }

    redirect_with_status('session-login.php', 'danger', 'Identifiants invalides pour la session PHP.');
}

render_header('Connexion session PHP', 'session');
?>
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Connexion via session PHP</h1>
                <p class="text-muted small">
                    Cette page déclenche une session PHP classique. Après la connexion, utilisez la section "Session PHP" pour contrôler la persistance à travers le reverse proxy.
                </p>
                <form method="post" class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="username">Utilisateur</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= TEST_USER ?>" required autocomplete="off">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="password">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" value="<?= TEST_PASSWORD ?>" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">Ouvrir la session PHP</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
render_footer();
