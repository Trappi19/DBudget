<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/auth.php';

requireLogin();


$title = trans('accounts.title');
$page_name = trans('accounts.nav');

require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/accounts.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/footer.php';
