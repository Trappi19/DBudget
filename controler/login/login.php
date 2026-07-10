<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/database/tables/user.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/auth.php');

$title = "Login";
if (isset($_POST['input_email']) && isset($_POST['input_password'])) {
    $email = $_POST['input_email'];
    $password = $_POST['input_password'];

    if (User::checkLogin($email, $password)) {
        Auth::issueForUser($email);
        header("Location: " . login_redirect_target());
        exit();
    } else {
        $error = 1;
    }
} else {
    $error = 0;
}

require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/login.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/index.php';
