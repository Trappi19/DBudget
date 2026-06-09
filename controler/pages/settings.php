<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/auth.php';

requireLogin();

$title = trans('settings.title');
$page_name = trans('settings.nav');

$username = $_SESSION['username'] ?? '';

$languages = get_available_languages();
$current_lang = get_locale();


require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/settings.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/footer.php';