<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/auth.php';

requireLogin();


$title = trans('verification.title');
$page_name = trans('verification.nav');

require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/verification.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/footer.php';
