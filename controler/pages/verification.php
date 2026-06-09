<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/auth.php';

requireLogin();


$title = t('page_title.verification');
$page_name = t('page_title.verification');

require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/verification.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/footer.php';
