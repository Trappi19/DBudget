<?php
header('Content-Type: application/json');
require($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/api/v1/validate.php');


if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['code' => 405, 'message' => 'Method not allowed', 'data' => []]);
    exit;
}

$id_account = $_GET['id_account'] ?? null;
$start = $_GET['start'] ?? null;
$end = $_GET['end'] ?? null;

checkRequiredArg(['id_account' => $id_account, 'start' => $start, 'end' => $end], ['id_account', 'start', 'end']);

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
echo json_encode(['code' => 200, 'message' => 'OK', 'data' => $query->fetchAll(PDO::FETCH_ASSOC)]);
