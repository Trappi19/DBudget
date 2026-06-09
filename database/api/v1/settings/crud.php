<?php
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/tables/user.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/api/v1/apiUtils.php');


if ($method === 'GET') {
    $langs = [];
    foreach (glob($_SERVER['DOCUMENT_ROOT'] . '/lang/*.json') as $file) {
        $name = basename($file, '.json'); // "Français.json" -> "Français"
        $langs[] = ['code' => $name, 'label' => $name];
    }
    sendAPIResponse(200, 'OK', ['languages' => $langs, 'current' => $_SESSION['lang'] ?? 'Français']);
}

if ($method !== 'PATCH') {
    sendAPIResponse(405, 'Method not allowed', []);
}

$username = sanitize_string($body['username'] ?? null, 50);
if ($username === false) {
    sendAPIResponse(400, 'Invalid or missing username', []);
}

$user = new User($_SESSION['email']);
$user->setUsername($username);

if (isset($body['lang'])) {
    $lang = sanitize_string($body['lang'], 50);
    $user->setLang($lang);
    $_SESSION['lang'] = $user->getLang();
}

$user->update();

$_SESSION['username'] = $username;

sendAPIResponse(200, 'Settings updated', []);