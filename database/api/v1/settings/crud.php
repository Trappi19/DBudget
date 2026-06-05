<?php
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/tables/user.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/api/v1/validate.php');


if ($method !== 'PATCH') {
    http_response_code(405);
    echo json_encode(['code' => 405, 'message' => 'Method not allowed', 'data' => []]);
    exit;
}

$username = sanitize_string($body['username'] ?? null, 50);
if ($username === false) {
    http_response_code(400);
    echo json_encode(['code' => 400, 'message' => 'Invalid or missing username', 'data' => []]);
    exit;
}

$user = new User($_SESSION['email']);
$user->setUsername($username);
$user->update();

$_SESSION['username'] = $username;

http_response_code(200);
echo json_encode(['code' => 200, 'message' => 'Settings updated', 'data' => []]);
