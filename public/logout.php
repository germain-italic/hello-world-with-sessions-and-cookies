<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

destroy_session();
clear_auth_cookie();

redirect_with_status('index.php', 'info', 'Sessions et cookies de test supprimés.');
