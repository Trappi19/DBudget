<?php

header('Content-Type: application/json');
require($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/api/v1/apiUtils.php');


if ($method !== 'GET') {
    sendAPIResponse(405, 'Method not allowed', []);
}

$id_account = $_GET['id_account'] ?? null;
$start = $_GET['start'] ?? null;
$end = $_GET['end'] ?? null;

checkRequiredArg(['id_account' => $id_account, 'start' => $start, 'end' => $end], ['id_account', 'start', 'end']);

if (!Auth::ownsAccount((int) $id_account)) {
    sendAPIResponse(403, 'Forbidden', []);
}

// GET /api/v1/accounts/operations?id_account=...&start=...&end=...
$query = $db->prepare(
    'SELECT id_operation, label, date, amount, new_sold, category
     FROM operation
     WHERE id_account = :id_account
     AND date >= :start
     AND date <= :end
     ORDER BY date ASC'
);
$query->execute(['id_account' => $id_account, 'start' => $start, 'end' => $end]);
sendAPIResponse(200, 'OK', $query->fetchAll(PDO::FETCH_ASSOC), false);
