<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/auth.php';

requireLogin();

$title = trans('error_404.title');
$page_name = trans('error_404.nav');

$error_code = 404;
$error_message = trans('error_404.error_message');
$error_description = trans('error_404.error_description');

http_response_code(404);

require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/error_page.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/footer.php';