<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/lang.php');

if (session_status() !== PHP_SESSION_ACTIVE) session_start();


if (isset($_SESSION['email'])) {
    header('Location: /app/home');
    exit();
}


if (isset($_GET['lang']) && in_array($_GET['lang'], get_available_language_codes(), true)) {
    $_SESSION['lang'] = $_GET['lang'];
}

$title = trans('auth.register.title');

require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/create_account.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/index.php';
