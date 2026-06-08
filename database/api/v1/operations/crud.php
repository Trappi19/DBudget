<?php
header('Content-Type: application/json');
require($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');
require($_SERVER['DOCUMENT_ROOT'] . '/database/tables/operation.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/api/v1/apiUtils.php');



// GET id with query string, otherwise JSON Body
if ($method === 'GET') {
    $id = isset($_GET['id']) ? sanitize_body(['id' => $_GET['id']])['id'] : null;
} else {
    $id = isset($body['id']) ? $body['id'] : null;
}


if ($method === 'GET') {

    // GET /api/v1/operations?id=X
    if ($id !== null) {

        $query = $db->prepare('SELECT * FROM operation WHERE id_operation = :id');
        $query->execute(['id' => $id]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            sendAPIResponse(404, 'Operation not found', []);
        }

        sendAPIResponse(200, 'OK', $result);
    }

    // GET /api/v1/operations?accounts=[...]&date=...&limit=...&label=...&category=...&regularity=...
    $accounts = json_decode($_GET['accounts'] ?? '[]');
    $date = sanitize_date($_GET['date'] ?? date('Y-m-d'));
    $where = 'WHERE id_account IN (' . implode(',', array_fill(0, count($accounts), '?')) . ') AND date <= ?';
    $params = array_merge($accounts, [$date]);
    $limit = isset($_GET['limit']) ? ' LIMIT ' . (int)$_GET['limit'] : '';

    if (empty($accounts)) {
        sendAPIResponse(200, 'OK', []);
    }
    foreach ($accounts as $account) {
        $account = sanitize_int($account ?? 0);
    }

    if (isset($_GET['regularity'])) {
        $regularity = sanitize_int($_GET['regularity']);
        if ($regularity == 0) sendAPIResponse(400, 'Invalid regularity', []);
        $where .= ' AND regularity = ?';
        $params[] = $regularity;
    }

    if (isset($_GET['label']) && $_GET['label'] !== '') {
        $label_filter = '%' . sanitize_string($_GET['label']) . '%';
        $where .= ' AND label LIKE ?';
        $params[] = $label_filter;
    }

    if (isset($_GET['category'])) {
        $where .= ' AND category = ?';
        $params[] = sanitize_int($_GET['category']);
    }

    $query = $db->prepare('SELECT * FROM operation ' . $where . ' ORDER BY date DESC' . $limit);
    $query->execute($params);
    sendAPIResponse(200, 'OK', $query->fetchAll(PDO::FETCH_ASSOC));
}

// POST /api/v1/operations
if ($method === 'POST') {
    ['label' => $label, 'date' => $date, 'amount' => $amount, 'category' => $category, 'id_account' => $id_account] = checkRequiredArg($body, ['label', 'date', 'amount', 'category', 'id_account']);

    $date = sanitize_date($date ?? '');
    Operation::createOperation($label, $date, $amount, $category, 0, $id_account);

    sendAPIResponse(201, 'Operation created', []);
}

checkRequiredArg(['id' => $id], ['id']);

// DELETE /api/v1/operations
if ($method === 'DELETE') {
    Operation::deleteOperation($id);

    sendAPIResponse(200, 'Operation deleted', []);
}

sendAPIResponse(405, 'Method not allowed', []);
