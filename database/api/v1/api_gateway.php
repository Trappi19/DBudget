<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/database/api/v1/apiUtils.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/auth.php');
requireLoginApi();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Parse body for POST, PATCH, DELETE, PUT
$body = [];
if (in_array($method, ['POST', 'PATCH', 'DELETE', 'PUT'])) {
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);
    if ($decoded === null && $raw !== '' && $raw !== 'null') {
        sendAPIResponse(400, 'Invalid JSON', []);
    }
    $body = sanitize_body($decoded ?? []);
}

$apiRoutes = [
    '/api/v1/accounts'              => 'database/api/v1/accounts/crud.php',
    '/api/v1/accounts/operations'   => 'database/api/v1/accounts/operations.php',
    '/api/v1/accounts/balance'      => 'database/api/v1/accounts/balance.php',

    '/api/v1/operations'            => 'database/api/v1/operations/crud.php',
    '/api/v1/operations/types'      => 'database/api/v1/operations/types.php',
    '/api/v1/operations/transaction' => 'database/api/v1/operations/transaction.php',

    '/api/v1/events'                => 'database/api/v1/events/crud.php',

    '/api/v1/settings'              => 'database/api/v1/settings/crud.php',
];

if (isset($apiRoutes[$uri])) {
    require($_SERVER['DOCUMENT_ROOT'] . '/' . $apiRoutes[$uri]);
    exit;
}

sendAPIResponse(404, 'API route not found', []);