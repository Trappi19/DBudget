<?php
header('Content-Type: application/json');
require($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');
require($_SERVER['DOCUMENT_ROOT'] . '/database/tables/operation.php');
require($_SERVER['DOCUMENT_ROOT'] . '/database/tables/account.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/api/v1/apiUtils.php');

// POST /api/v1/operations/transaction
if ($method !== 'POST') {
    sendAPIResponse(405, 'Method not allowed', []);
}

['from' => $from_id, 'to' => $to_id, 'label' => $label, 'date' => $date, 'amount' => $amount] = checkRequiredArg($body, ['from', 'to', 'label', 'date', 'amount']);

if ($amount == 0 || $from_id == $to_id) {
    sendAPIResponse(201, 'No transaction created', []);
}

$from = new Account($from_id);
$to = new Account($to_id);

$order = null;
if ($from->getType() == 0 && $to->getType() == 1) { $order = 0; }
if ($from->getType() == 1 && $to->getType() == 1) { $order = 6; }
else if ($to->getType() == 0) { $order = 7; }

Operation::createOperation($label, $date, -$amount, $order, 0, $from_id);
Operation::createOperation($label, $date,  $amount, $order, 0, $to_id);

sendAPIResponse(201, 'Transaction created', []);