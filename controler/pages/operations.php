<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/auth.php';

requireLogin();


$title = t('page_title.operations');
$page_name = t('page_title.operations');

require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/operations.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/footer.php';
