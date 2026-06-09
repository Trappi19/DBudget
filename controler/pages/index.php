<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/auth.php';

// Starting Session
requireLogin();

// Variables page
$title = t('page_title.home');
$page_name = t('nav.overview');

// Show Template
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/index.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/footer.php';
