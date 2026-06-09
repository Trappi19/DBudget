<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/auth.php';

requireLogin();


$title = trans('events.title');
$page_name = trans('events.nav');

require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/events.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/footer.php';
