<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';

$routes = [
    // Pages
    '/app/home'      => '/controler/pages/index.php',
    '/app/accounts'   => '/controler/pages/accounts.php',
    '/app/operations' => '/controler/pages/operations.php',
    '/app/verification' => '/controler/pages/verification.php',
    '/app/events'     => '/controler/pages/events.php',
    '/app/budget'     => '/controler/pages/budget.php',
    '/app/analytics'  => '/controler/pages/analytics.php',
    '/app/settings'   => '/controler/pages/settings.php',
    '/app/login'      => '/controler/login/login.php',
    '/app/logout'     => '/controler/login/logout.php',

    // API - GET
    '/api/get/accounts'          => '/database/api/get_accounts_by_user.php',
    '/api/get/amount'            => '/database/api/get_amount_at_date.php',
    '/api/get/events'            => '/database/api/get_events_by_accounts.php',
    '/api/get/operation-types'   => '/database/api/get_operation_type_list.php',
    '/api/get/operations'        => '/database/api/get_operations_by_accounts.php',
    '/api/get/operations-account'=> '/database/api/get_operations_by_account.php',

    // API - CREATE
    '/api/create/account'     => '/controler/creating_elements/account.php',
    '/api/create/event'       => '/controler/creating_elements/event.php',
    '/api/create/operation'   => '/controler/creating_elements/operation.php',
    '/api/create/transaction' => '/controler/creating_elements/transaction.php',

    // API - UPDATE
    '/api/update/account' => '/controler/updating_elements/account.php',
    '/api/update/event'   => '/controler/updating_elements/event.php',
    '/api/update/settings' => '/controler/updating_elements/settings.php',

    // API - DELETE
    '/api/delete/account'   => '/controler/deleting_elements/account.php',
    '/api/delete/event'     => '/controler/deleting_elements/event.php',
    '/api/delete/operation' => '/controler/deleting_elements/operation.php',
];

if ($uri === '/') {
    header('Location: /app/home');
    exit();
} elseif (isset($routes[$uri])) {
    require $_SERVER['DOCUMENT_ROOT'] . $routes[$uri];
} else {
    http_response_code(404);
    echo "404 - Page non trouvée";
}