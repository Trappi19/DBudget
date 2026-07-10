<?php

header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/tables/operation.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/api/v1/apiUtils.php');


$id = $_GET['id_account'] ?? null;
$date = $_GET['date'] ?? date('Y-m-d');

checkRequiredArg(['id_account' => $id], ['id_account']);

if (!Auth::ownsAccount((int) $id)) {
    sendAPIResponse(403, 'Forbidden', []);
}

$balance = Operation::getLastOperationSoldByAccount($id, $date);

sendAPIResponse(200, 'OK', ['balance' => $balance]);
