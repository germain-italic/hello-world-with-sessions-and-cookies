# Hello World Reverse Proxy Lab

Mini site de test en PHP/Apache pour valider le comportement d'un reverse proxy (nginx).

## Fonctionnalités

- Parcours de test guidé (checklist) côté interface.
- Connexion « privée » via session PHP (`$_SESSION`) avec manipulation de variables.
- Connexion par cookie JSON personnalisé (lecture, rafraîchissement, mutation, suppression).
- Upload multi-part avec stockage sur disque et liste des fichiers déposés.
- Download de fichier statique et download de fichiers uploadés (types exécutables bloqués côté upload).
- Page de diagnostic HTTPS/SSL pour contrôler les en-têtes `X-Forwarded-*` et la terminaison TLS.
- Bouton de déconnexion qui purge session et cookie (regénération d'identifiant de session lors du login).
- UI basée sur Bootstrap 5 pour une ergonomie rapide.

## Démarrage rapide (développement)

```bash
php -S 0.0.0.0:8080 -t public
```

Ensuite ouvrez <http://localhost:8080>.

## Déploiement Apache

Définir le `DocumentRoot` sur le dossier `public/`.

```apache
<VirtualHost *:80>
    ServerName hello-proxy.local
    DocumentRoot /var/www/hello-world-with-sessions-and-cookies/public

    <Directory /var/www/hello-world-with-sessions-and-cookies/public>
        AllowOverride None
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/hello-proxy-error.log
    CustomLog ${APACHE_LOG_DIR}/hello-proxy-access.log combined
</VirtualHost>
```

Assurez-vous que l'utilisateur Apache possède les droits d'écriture sur `uploads/`.

```bash
sudo chown www-data:www-data uploads
sudo chmod 775 uploads
```

## Suggestions de configuration nginx (reverse proxy)

Face à Apache :

```nginx
location / {
    proxy_pass http://127.0.0.1:8080;
    proxy_set_header Host $host;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header X-Forwarded-Port $server_port;
    proxy_cookie_path / "/; secure; HttpOnly";
}
```

Points de contrôle recommandés :

1. **Sessions PHP** : connecter l'utilisateur (`devops` / `proxy123`), naviguer sur plusieurs pages, vérifier que le cookie PHPSESSID reste identique.
2. **Manipulation de session** : ajouter/modifier des clés depuis la page « Session PHP » et vérifier la cohérence après rafraîchissement.
3. **Cookie custom** : lancer le parcours cookie, modifier manuellement le JSON dans le navigateur (ex. via DevTools) puis observer la réaction côté serveur.
4. **Upload** : déposer un fichier >1 MB pour contrôler la taille via le proxy et vérifier l'écriture sur disque.
5. **Download** : récupérer `proxy-test.txt` puis comparer la somme de contrôle en sortie du reverse proxy.
6. **HTTPS** : ouvrir « Check HTTPS » et confirmer la détection d'HTTPS via les variables serveur et les en-têtes `X-Forwarded-*`.
7. **Déconnexion** : utiliser le bouton « Déconnexion », s'assurer que la session et le cookie sont invalidés, puis tester un accès direct aux pages privées.

## Structure

- `public/` : racine web (pages PHP, contrôleurs de download).
- `public/includes/` : helpers et configuration.
- `uploads/` : stockage des fichiers déposés (ignored par Git).
- `downloads/` : fichier statique de test.

## Maintenance

- Les pages utilisent Bootstrap via CDN ; vérifier que le proxy laisse passer les ressources externes ou prévoir un mirroring local.
- `helpers.php` centralise la logique d'authentification (session et cookie) et regénère l'ID de session à chaque login pour éviter la fixation.
- Exécuter `php -S ...` ou un hôte Apache pour valider la syntaxe PHP (PHP 8+ requis).
- L'upload est plafonné à 5 MB, les extensions exécutables (`.php`, `.sh`, `.exe`, etc.) sont rejetées et les fichiers sont stockés hors racine web (`uploads/`) ; les téléchargements passent via `download-upload.php` avec `Content-Disposition: attachment` et `X-Content-Type-Options: nosniff`.
