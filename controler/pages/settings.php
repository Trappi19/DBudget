<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php';

requireLogin();

$title = trans('settings.title');
$page_name = trans('settings.nav');

$username = $_SESSION['username'] ?? '';
$email = $_SESSION['email'] ?? '';
$mail_contact = getenv('MAIL_CONTACT') ?: '';

$languages = get_available_languages();
$current_lang = get_locale();


require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/settings.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/footer.php';
