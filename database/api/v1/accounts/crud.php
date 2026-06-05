<?php
header('Content-Type: application/json');
require($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');
require($_SERVER['DOCUMENT_ROOT'] . '/database/tables/account.php');
require($_SERVER['DOCUMENT_ROOT'] . '/database/tables/operation.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/api/v1/validate.php');



// GET /api/v1/accounts        -> liste
// GET /api/v1/accounts?id=X   -> item
if ($method === 'GET') {
    $id = isset($_GET['id']) ? sanitize_body(['id' => $_GET['id']])['id'] : null;

    if ($id !== null) {
        checkRequiredArg(['id' => $id], ['id'], ['id']); // $id vient de $_GET

        $query = $db->prepare('SELECT id_account, label, type FROM bank_account WHERE id_account = :id AND user_email = :email');
        $query->execute(['id' => $id, 'email' => $_SESSION['email']]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            http_response_code(404);
            echo json_encode(['code' => 404, 'message' => 'Account not found', 'data' => []]);
            exit;
        }

        $result['sold'] = Operation::getLastOperationSoldByAccount($id, date('Y-m-d'));
        echo json_encode(['code' => 200, 'message' => 'OK', 'data' => $result]);
        exit;
    } else {
        $query = $db->prepare('SELECT id_account, label, type FROM bank_account WHERE user_email = :email ORDER BY type ASC');
        $query->execute(['email' => $_SESSION['email']]);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $key => $value) {
            $result[$key]['sold'] = Operation::getLastOperationSoldByAccount($value['id_account'], date('Y-m-d'));
        }

        echo json_encode(['code' => 200, 'message' => 'OK', 'data' => $result]);
        exit;
    }
}

// POST /api/v1/accounts -> creation
if ($method === 'POST') {
    $body['sold'] = $body['sold'] ?: 0;
    ['label' => $label, 'type' => $type, 'sold' => $sold] = checkRequiredArg($body, ['label', 'type', 'sold']);

    $id_account = Account::createAccount($label, $type, $_SESSION['email']);

    if ($sold != 0) {
        Operation::createOperation("Init " . $label . " sold", "1999-01-01", $sold, 5, 0, $id_account);
    }

    http_response_code(201);
    echo json_encode(['code' => 201, 'message' => 'Account created', 'data' => []]);
    exit;
}

// PATCH /api/v1/accounts
if ($method === 'PATCH') {
    ['id' => $id, 'label' => $label, 'type' => $type, 'sold' => $sold] = checkRequiredArg($body, ['id', 'label', 'type', 'sold'], ['id']);

    $account = new Account($id);
    $account->setLabel($label);
    $account->setType($type);

    $prev_sold = Operation::getLastOperationSoldByAccount($id, date('Y-m-d'));
    if ($sold - $prev_sold != 0) {
        Operation::createOperation("Balance update", date('Y-m-d'), $sold - $prev_sold, 6, 0, $id);
    }

    $account->update();

    http_response_code(200);
    echo json_encode(['code' => 200, 'message' => 'Account updated', 'data' => []]);
    exit;
}

// DELETE /api/v1/accounts
if ($method === 'DELETE') {
    ['id' => $id] = checkRequiredArg($body, ['id'], ['id']);

    Account::deleteAccount($id);

    http_response_code(200);
    echo json_encode(['code' => 200, 'message' => 'Account deleted', 'data' => []]);
    exit;
}

http_response_code(405);
echo json_encode(['code' => 405, 'message' => 'Method not allowed', 'data' => []]);
