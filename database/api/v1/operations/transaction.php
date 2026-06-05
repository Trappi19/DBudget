<?php
header('Content-Type: application/json');
require($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');
require($_SERVER['DOCUMENT_ROOT'] . '/database/tables/operation.php');
require($_SERVER['DOCUMENT_ROOT'] . '/database/tables/account.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/api/v1/validate.php');

// POST /api/v1/operations/transaction
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['code' => 405, 'message' => 'Method not allowed', 'data' => []]);
    exit;
}

['from' => $from_id, 'to' => $to_id, 'label' => $label, 'date' => $date, 'amount' => $amount] = checkRequiredArg($body, ['from', 'to', 'label', 'date', 'amount']);

$from = new Account($from_id);
$to = new Account($to_id);

$order = null;
if ($from->getType() == 0 && $to->getType() == 1) { $order = 0; }
if ($from->getType() == 1 && $to->getType() == 1) { $order = 6; }
else if ($to->getType() == 0) { $order = 7; }

Operation::createOperation($label, $date, -$amount, $order, 0, $from_id);
Operation::createOperation($label, $date,  $amount, $order, 0, $to_id);

http_response_code(201);
echo json_encode(['code' => 201, 'message' => 'Transaction created', 'data' => []]);
