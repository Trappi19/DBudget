<?php

if ($method !== 'POST') {
    sendAPIResponse(405, 'Method not allowed', []);
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/tables/user.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/tables/pending_account.php');

checkRequiredArg($body, ['email', 'code']);

$email = htmlspecialchars_decode($body['email'], ENT_QUOTES);
$code = htmlspecialchars_decode($body['code'], ENT_QUOTES);

$pending = PendingAccount::find($email);

if ($pending === null) {
    sendAPIResponse(401, 'Invalid code', []);
}

// Anti brute-force
if ((int)$pending['attempts'] >= 1) {
    sleep(2);
}

// expired code = invalide.
if (strtotime($pending['expires_at']) < time()) {
    PendingAccount::delete($email);
    sendAPIResponse(401, 'Code expired', []);
}

if (!password_verify($code, $pending['code_hash'])) {
    PendingAccount::incrementAttempts($email);
    sendAPIResponse(401, 'Invalid code', []);
}

if (User::exists($email)) {
    PendingAccount::delete($email);
    sendAPIResponse(409, 'Email already used', []);
}

User::register(
    $email,
    $pending['username'],
    $pending['password'],
    $pending['salt'],
    $pending['lang']
);

PendingAccount::delete($email);

$_SESSION['email'] = $email;
$_SESSION['username'] = $pending['username'];
$_SESSION['lang'] = $pending['lang'];

sendAPIResponse(201, 'Account created', ['redirect' => '/app/home']);
