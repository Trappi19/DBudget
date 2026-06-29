<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/lang.php');

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Already authenticated users have no business on the registration page.
if (isset($_SESSION['email'])) {
    header('Location: /app/home');
    exit();
}

// The language is picked on the first step. Persisting it in the session makes
// the whole page (and the server-side messages) render in that language. The
// chosen value is also sent with the registration request and saved on the
// account's settings.
if (isset($_GET['lang'])) {
    $allowed = array_map(
        fn($f) => basename($f, '.json'),
        glob($_SERVER['DOCUMENT_ROOT'] . '/lang/*.json')
    );
    if (in_array($_GET['lang'], $allowed, true)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
}

$title = trans('auth.register.title');

require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/create_account.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/index.php';
