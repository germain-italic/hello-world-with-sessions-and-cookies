<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

render_header('Accueil', 'home');
?>
<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="card-title h3 mb-3">Hello world · Reverse proxy lab</h1>
                <p class="text-muted mb-4">
                    Ce mini site permet de valider le comportement d'un reverse proxy (nginx) devant une application PHP/Apache.
                    Chaque parcours couvre les points classiques : sessions PHP, cookies personnalisés, transferts de fichiers et gestion de déconnexion.
                </p>
                <h2 class="h5">Parcours de test</h2>
                <?php render_test_steps(); ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5">Identifiants de test</h2>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Login</dt>
                    <dd class="col-sm-8"><code><?= TEST_USER ?></code></dd>
                    <dt class="col-sm-4">Mot de passe</dt>
                    <dd class="col-sm-8"><code><?= TEST_PASSWORD ?></code></dd>
                </dl>
                <p class="small text-muted mt-3">
                    Utilisez ces identifiants pour les deux parcours. Les erreurs sont volontairement explicites pour faciliter le debugging à travers le proxy.
                </p>
            </div>
        </div>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5">Connexion via session PHP</h2>
                <form method="post" action="session-login.php" class="row g-2">
                    <input type="hidden" name="mode" value="session">
                    <div class="col-12">
                        <label class="form-label" for="session-username">Utilisateur</label>
                        <input type="text" class="form-control" id="session-username" name="username" value="<?= TEST_USER ?>" autofocus>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="session-password">Mot de passe</label>
                        <input type="password" class="form-control" id="session-password" name="password" value="<?= TEST_PASSWORD ?>">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary w-100" type="submit">Démarrer la session PHP</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5">Connexion via cookie</h2>
                <form method="post" action="cookie-login.php" class="row g-2">
                    <input type="hidden" name="mode" value="cookie">
                    <div class="col-12">
                        <label class="form-label" for="cookie-username">Utilisateur</label>
                        <input type="text" class="form-control" id="cookie-username" name="username" value="<?= TEST_USER ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="cookie-password">Mot de passe</label>
                        <input type="password" class="form-control" id="cookie-password" name="password" value="<?= TEST_PASSWORD ?>">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-outline-primary w-100" type="submit">Créer le cookie d'authentification</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
render_footer();
