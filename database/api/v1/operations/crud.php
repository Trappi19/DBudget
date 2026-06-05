<?php
header('Content-Type: application/json');
require($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');
require($_SERVER['DOCUMENT_ROOT'] . '/database/tables/operation.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/api/v1/validate.php');



// GET id with query string, otherwise JSON Body
if ($method === 'GET') {
    $id = isset($_GET['id']) ? sanitize_body(['id' => $_GET['id']])['id'] : null;
} else {
    $id = isset($body['id']) ? $body['id'] : null;
}

// GET /api/v1/operations?accounts=[...]&date=...&limit=...   -> liste
// GET /api/v1/operations?id=X                                -> item
if ($method === 'GET') {
    if ($id !== null) {
        checkRequiredArg(['id' => $id], ['id'], ['id']);

        $query = $db->prepare('SELECT * FROM operation WHERE id_operation = :id');
        $query->execute(['id' => $id]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            http_response_code(404);
            echo json_encode(['code' => 404, 'message' => 'Operation not found', 'data' => []]);
            exit;
        }

        echo json_encode(['code' => 200, 'message' => 'OK', 'data' => $result]);
        exit;
    }

    $arg = json_decode($_GET['accounts'] ?? '[]');
    $date = sanitize_date(sanitize_body(['date' => $_GET['date'] ?? date('Y-m-d')])['date']);
    $limit = isset($_GET['limit']) ? ' LIMIT ' . (int)$_GET['limit'] : '';

    if ($date === false) {
        http_response_code(400);
        echo json_encode(['code' => 400, 'message' => 'Invalid date format (expected Y-m-d)', 'data' => []]);
        exit;
    }

    $accounts = [];
    foreach ($arg as $value) {
        $id = sanitize_body(['id' => $value->id_account ?? null])['id'];
        if ($id !== false && $id > 0) $accounts[] = $id;
    }

    if (empty($accounts)) {
        echo json_encode(['code' => 200, 'message' => 'OK', 'data' => []]);
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($accounts), '?'));

    if (isset($_GET['regularity'])) {
        $regularity = sanitize_body(['regularity' => $_GET['regularity']])['regularity'];
        if ($regularity === false) {
            http_response_code(400);
            echo json_encode(['code' => 400, 'message' => 'Invalid regularity', 'data' => []]);
            exit;
        }
        $params = array_merge($accounts, [$regularity, $date]);
        $query = $db->prepare(
            'SELECT * FROM operation
             WHERE id_account IN (' . $placeholders . ')
             AND regularity = ?
             AND date <= ?
             ORDER BY date DESC' . $limit
        );
    } else {
        $params = array_merge($accounts, [$date]);
        $query = $db->prepare(
            'SELECT * FROM operation
             WHERE id_account IN (' . $placeholders . ')
             AND date <= ?
             ORDER BY date DESC' . $limit
        );
    }

    $query->execute($params);
    echo json_encode(['code' => 200, 'message' => 'OK', 'data' => $query->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

// POST /api/v1/operations
if ($method === 'POST') {
    ['label' => $label, 'date' => $date, 'amount' => $amount, 'category' => $category, 'id_account' => $id_account] = checkRequiredArg($body, ['label', 'date', 'amount', 'category', 'id_account'], ['id_account']);

    $date = sanitize_date($date ?? '');
    if ($date === false) {
        http_response_code(400);
        echo json_encode(['code' => 400, 'message' => 'Invalid date format (expected Y-m-d)', 'data' => []]);
        exit;
    }

    Operation::createOperation($label, $date, $amount, $category, 0, $id_account);

    http_response_code(201);
    echo json_encode(['code' => 201, 'message' => 'Operation created', 'data' => []]);
    exit;
}

checkRequiredArg(['id' => $id], ['id'], ['id']);

// DELETE /api/v1/operations
if ($method === 'DELETE') {
    Operation::deleteOperation($id);

    http_response_code(200);
    echo json_encode(['code' => 200, 'message' => 'Operation deleted', 'data' => []]);
    exit;
}

http_response_code(405);
echo json_encode(['code' => 405, 'message' => 'Method not allowed', 'data' => []]);
