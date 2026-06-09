<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/api/v1/apiUtils.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/lang.php');

function requireLogin() {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (!isset($_SESSION['email'])) {
        $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
        header('Location: /app/login');
        exit();
    }
}

function requireLoginApi() {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (!isset($_SESSION['email'])) {
        sendAPIResponse(401, 'Not logged in', []);
    }
}