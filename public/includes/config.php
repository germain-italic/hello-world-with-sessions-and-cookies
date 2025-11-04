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

// Trusted reverse proxies permitted to access proxy-only diagnostics.
const TRUSTED_PROXY_IPS = PROXY_CONFIG['trusted_proxy_ips'] ?? [];

// Checklist categories surfaced on the UI to help DevOps teams validate leur reverse proxy.
const TEST_CATEGORIES = [
    [
        'key' => 'security',
        'title' => 'Sécurité & En-têtes',
        'items' => [
            [
                'key' => 'headers',
                'title' => 'Headers & Cache',
                'description' => 'Inspecter les en-têtes de sécurité, cache, ETag/Last-Modified et protocole HTTP.',
                'link' => 'headers-lab.php',
            ],
            [
                'key' => 'cors',
                'title' => 'CORS Lab',
                'description' => 'Valider les en-têtes Access-Control-* et la gestion des pré-vols.',
                'link' => 'cors-lab.php',
            ],
            [
                'key' => 'basic-auth',
                'title' => 'Basic Auth',
                'description' => 'Tester le passage des en-têtes Authorization à travers le proxy.',
                'link' => 'basic-auth-lab.php',
            ],
        ],
    ],
    [
        'key' => 'sessions',
        'title' => 'Sessions & Interactions',
        'items' => [
            [
                'key' => 'session',
                'title' => 'Session PHP',
                'description' => 'Se connecter et vérifier la persistance de session via plusieurs pages.',
                'link' => 'session-login.php',
            ],
            [
                'key' => 'cookie',
                'title' => 'Session Cookie',
                'description' => 'Manipuler le cookie d\'authentification personnalisé et observer les réactions serveur.',
                'link' => 'cookie-login.php',
            ],
            [
                'key' => 'transfer',
                'title' => 'Upload & Download',
                'description' => 'Uploader un fichier, le retélécharger et vérifier l’intégrité à travers le proxy.',
                'link' => 'transfer-lab.php',
            ],
            [
                'key' => 'logout',
                'title' => 'Déconnexion',
                'description' => 'Purger session et cookie de test puis vérifier l’accès direct.',
                'link' => 'logout.php',
            ],
        ],
    ],
    [
        'key' => 'network',
        'title' => 'Réseau & Proxy',
        'items' => [
            [
                'key' => 'ssl',
                'title' => 'Check HTTPS',
                'description' => 'Contrôler la terminaison TLS et les en-têtes X-Forwarded-*.',
                'link' => 'ssl-check.php',
            ],
            [
                'key' => 'network',
                'title' => 'Trace IP',
                'description' => 'Comparer REMOTE_ADDR, X-Forwarded-For et X-Real-IP.',
                'link' => 'network-trace.php',
            ],
            [
                'key' => 'gzip',
                'title' => 'Compression',
                'description' => 'Vérifier la compression gzip/brotli et la cohérence Content-Encoding / Content-Length.',
                'link' => 'gzip-lab.php',
            ],
            [
                'key' => 'proxy',
                'title' => 'Proxy Only',
                'description' => 'Accéder à la zone restreinte uniquement via le reverse proxy autorisé.',
                'link' => 'proxy-only.php',
            ],
        ],
    ],
    [
        'key' => 'routing',
        'title' => 'Routage & Intégrité',
        'items' => [
            [
                'key' => 'rewrite',
                'title' => 'Rewrite Lab',
                'description' => 'Tester les règles .htaccess et vérifier la capture des slugs.',
                'link' => 'rewrite-lab.php',
            ],
            [
                'key' => 'absolute',
                'title' => 'Liens absolus',
                'description' => 'Valider la réécriture des URLs absolues et des assets par le proxy.',
                'link' => 'absolute-links-lab.php',
            ],
            [
                'key' => 'redirects',
                'title' => 'Redirections',
                'description' => 'Contrôler les codes 301/302/307/308 et la cible Location.',
                'link' => 'redirect-lab.php',
            ],
            [
                'key' => 'fuzz',
                'title' => 'Robustesse & Fuzz',
                'description' => 'Envoyer des chemins encodés ou suspects pour détecter les altérations.',
                'link' => 'fuzz-lab.php',
            ],
        ],
    ],
];
