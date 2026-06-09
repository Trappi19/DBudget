<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/auth.php';

requireLogin();



$title = trans('budget.title');
$page_name = trans('budget.nav');

require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/budget.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/footer.php';
