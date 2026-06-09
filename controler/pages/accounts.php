<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/auth.php';

requireLogin();


$title = t('page_title.accounts');
$page_name = t('page_title.accounts');

require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/accounts.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/footer.php';
