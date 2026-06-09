<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/auth.php';

requireLogin();

$title = t('page_title.settings');
$page_name = t('page_title.settings');

$username = $_SESSION['username'] ?? '';


require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/settings.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/footer.php';