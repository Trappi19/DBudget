<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/database/tables/account.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/database/tables/operation_type.php';

requireLogin();

$accounts = Account::getAccountsByUser(Auth::email());
$operation_types = OperationType::getAll();

$title = trans('operations.title');
$page_name = trans('operations.nav');

require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/operations.php';
require $_SERVER['DOCUMENT_ROOT'] . '/public/view/helpers/footer.php';
