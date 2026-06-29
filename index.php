<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';

// Delegate all /api/* routes to the API sub-router
if (str_starts_with($uri, '/api/')) {
    require $_SERVER['DOCUMENT_ROOT'] . '/database/api/v1/api_gateway.php';
    exit;
}

$pageRoutes = [
    '/app/home'         => 'controler/pages/index.php',
    '/app/accounts'     => 'controler/pages/accounts.php',
    '/app/operations'   => 'controler/pages/operations.php',
    '/app/verification' => 'controler/pages/verification.php',
    '/app/events'       => 'controler/pages/events.php',
    '/app/budget'       => 'controler/pages/budget.php',
    '/app/analytics'    => 'controler/pages/analytics.php',
    '/app/settings'     => 'controler/pages/settings.php',
    '/app/login'          => 'controler/login/login.php',
    '/app/create-account' => 'controler/login/create_account.php',
    '/app/logout'         => 'controler/login/logout.php',
];

if (isset($pageRoutes[$uri])) {
    require $_SERVER['DOCUMENT_ROOT'] . '/' . $pageRoutes[$uri];
    exit;
}

if ($uri === '/') {
    header('Location: /app/home');
    exit();
} else {
    require $_SERVER['DOCUMENT_ROOT'] . '/controler/pages/error_404.php';
}
