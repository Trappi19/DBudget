<?php
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/tables/operation.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/api/v1/validate.php');


$id = $_GET['id_account'] ?? null;
$date = $_GET['date'] ?? date('Y-m-d');

checkRequiredArg(['id_account' => $id], ['id_account']);

$balance = Operation::getLastOperationSoldByAccount($id, $date);
echo json_encode(['code' => 200, 'message' => 'OK', 'data' => $balance]);
