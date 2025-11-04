<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === TEST_USER && $password === TEST_PASSWORD) {
        set_auth_cookie($username);
        redirect_with_status('cookie-dashboard.php', 'success', 'Cookie d\'authentification créé.');
    }

    redirect_with_status('cookie-login.php', 'danger', 'Identifiants invalides pour la session cookie.');
}

render_header('Connexion cookie', 'cookie');
?>
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Connexion via cookie dédié</h1>
                <p class="text-muted small">
                    Cette approche ne s'appuie pas sur <code>$_SESSION</code> mais sur un cookie JSON lisible.
                    Modifiez-le manuellement dans votre navigateur pour observer la réaction de l'application à travers le proxy.
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
                        <button type="submit" class="btn btn-outline-primary w-100">Créer le cookie</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
render_footer();
