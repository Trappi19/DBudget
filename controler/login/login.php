<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/database/tables/user.php');

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$title = "Login";
if (isset($_GET['input_email']) && isset($_GET['input_password'])) {
    $email = $_GET['input_email'];
    $password = $_GET['input_password'];

    if (User::checkLogin($email, $password)) {
        $user = new User($email);
        $_SESSION['email'] = $email;
        $_SESSION['username'] = $user->getUsername();
        $_SESSION['lang'] = $user->getLang();
        header("Location: " . ($_SESSION['redirect'] ?? '/app/home'));
        exit();
    } else {
        $error = 1;;
    }
} else {
    $error = 0;
}

require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/login.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/index.php';