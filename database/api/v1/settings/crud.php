<?php

header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/tables/user.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/api/v1/apiUtils.php');


if ($method !== 'PATCH') {
    sendAPIResponse(405, 'Method not allowed', []);
}

$username = sanitize_string($body['username'] ?? null, 50);
if ($username === false) {
    sendAPIResponse(400, 'Invalid or missing username', []);
}

$user = new User(Auth::email());
$user->setUsername($username);

if (isset($body['lang'])) {
    $lang = sanitize_string($body['lang'], 50);
    $user->setLang($lang);
}

$user->update();

// username and language are read from the database per request, so there is
// nothing session-side to keep in sync here.
sendAPIResponse(200, 'Settings updated', []);
