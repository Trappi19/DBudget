<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/auth.php';

requireLogin();


$title = trans('analytics.title');
$page_name = trans('analytics.nav');

require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/analytics.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/footer.php';
