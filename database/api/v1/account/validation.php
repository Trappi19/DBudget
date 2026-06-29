<?php

if ($method !== 'POST') {
    sendAPIResponse(405, 'Method not allowed', []);
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/tables/user.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/tables/pending_account.php');

checkRequiredArg($body, ['email', 'code']);

$email = htmlspecialchars_decode($body['email'], ENT_QUOTES);
$code  = htmlspecialchars_decode($body['code'], ENT_QUOTES);

$pending = PendingAccount::find($email);

if ($pending === null) {
    sendAPIResponse(401, 'Invalid code', []);
}

// From the 2nd attempt onward, slow the response down to deter brute force.
if ((int)$pending['attempts'] >= 1) {
    sleep(2);
}

// Expired codes are dropped and treated as invalid.
if (strtotime($pending['expires_at']) < time()) {
    PendingAccount::delete($email);
    sendAPIResponse(401, 'Code expired', []);
}

if (!password_verify($code, $pending['code_hash'])) {
    PendingAccount::incrementAttempts($email);
    sendAPIResponse(401, 'Invalid code', []);
}

// Guard against the account having been created in the meantime.
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

// Log the freshly created user in so the front can land on the home page.
$_SESSION['email']    = $email;
$_SESSION['username'] = $pending['username'];
$_SESSION['lang']     = $pending['lang'];

sendAPIResponse(201, 'Account created', ['redirect' => '/app/home']);
