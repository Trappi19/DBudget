<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/auth.php';

requireLogin();

$title = trans('error_403.title');
$page_name = trans('error_403.nav');

$error_code = 403;
$error_message = trans('error_403.error_message');
$error_description = trans('error_403.error_description');

http_response_code(403);

require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/error_page.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/footer.php';