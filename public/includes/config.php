<?php

declare(strict_types=1);

// Demo credentials displayed to the user on the login page.
const TEST_USER = 'devops';
const TEST_PASSWORD = 'proxy123';

// Cookie settings for the cookie-based auth flow.
const COOKIE_AUTH_NAME = 'proxy_auth_token';
const COOKIE_LIFETIME = 3600; // seconds

// Upload configuration (5 MB limit is enough for most proxy validation scenarios).
const MAX_UPLOAD_SIZE = 5 * 1024 * 1024;
const DISALLOWED_UPLOAD_EXTENSIONS = [
    'php',
    'phtml',
    'php3',
    'php4',
    'php5',
    'phps',
    'phar',
    'htaccess',
    'ini',
    'cgi',
    'pl',
    'exe',
    'com',
    'bat',
    'cmd',
    'sh',
];

// Checklist steps surfaced on the UI to help DevOps teams validate their reverse proxy.
const TEST_STEPS = [
    [
        'title' => 'Accès public',
        'description' => 'Naviguer sur la page d\'accueil proxifiée et vérifier que les assets Bootstrap sont chargés correctement.',
    ],
    [
        'title' => 'Connexion via session PHP',
        'description' => 'Se connecter avec les identifiants et vérifier la persistance de la session sur plusieurs pages.',
    ],
    [
        'title' => 'Connexion via cookie',
        'description' => 'Initier une session basée sur un cookie custom et contrôler lecture/écriture/modification.',
    ],
    [
        'title' => 'Manipulation de session',
        'description' => 'Ajouter, modifier et supprimer des variables de session et observer la propagation via le proxy.',
    ],
    [
        'title' => 'Manipulation de cookie',
        'description' => 'Modifier et supprimer manuellement le cookie d\'authentification pour vérifier la réaction côté serveur.',
    ],
    [
        'title' => 'Upload de fichier',
        'description' => 'Uploader un fichier et confirmer qu\'il est conservé et retéléchargeable à travers le proxy.',
    ],
    [
        'title' => 'Download statique',
        'description' => 'Télécharger le fichier de test pour vérifier les entêtes et l\'intégrité du contenu.',
    ],
    [
        'title' => 'Validation HTTPS',
        'description' => 'Contrôler que la connexion proxifiée est bien servie en HTTPS et que les en-têtes X-Forwarded-* sont cohérents.',
    ],
    [
        'title' => 'Déconnexion',
        'description' => 'Se déconnecter et confirmer la suppression de la session et du cookie, puis retenter un accès direct.',
    ],
];
