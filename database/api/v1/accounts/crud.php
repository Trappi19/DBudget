<?php
header('Content-Type: application/json');
require($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');
require($_SERVER['DOCUMENT_ROOT'] . '/database/tables/account.php');
require($_SERVER['DOCUMENT_ROOT'] . '/database/tables/operation.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/api/v1/apiUtils.php');



// GET /api/v1/accounts        -> liste
// GET /api/v1/accounts?id=X   -> item
if ($method === 'GET') {
    $id = $_GET['id'] ?? null;

    if ($id != null) {

        $query = $db->prepare('SELECT id_account, label, type FROM bank_account WHERE id_account = :id AND user_email = :email');
        $query->execute(['id' => $id, 'email' => $_SESSION['email']]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            sendAPIResponse(404, 'Account not found', []);
        }

        $result['sold'] = Operation::getLastOperationSoldByAccount($id, date('Y-m-d'));
        sendAPIResponse(200, 'OK', $result);
    } else {

        $query = $db->prepare('SELECT id_account, label, type, icon FROM bank_account WHERE user_email = :email ORDER BY type ASC');
        $query->execute(['email' => $_SESSION['email']]);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $key => $value) {
            $result[$key]['sold'] = Operation::getLastOperationSoldByAccount($value['id_account'], date('Y-m-d'));
        }

        sendAPIResponse(200, 'OK', $result);
    }
}

// POST /api/v1/accounts -> creation
if ($method === 'POST') {
    ['label' => $label, 'type' => $type] = checkRequiredArg($body, ['label', 'type']);
    $sold = $body['sold'] ?: 0;

    $id_account = Account::createAccount($label, $type, $_SESSION['email']);

    if ($sold != 0) {
        Operation::createOperation("Init " . $label . " sold", "1999-01-01", $sold, 6, 0, $id_account);
    }

    sendAPIResponse(201, 'Account created', []);
}

// PATCH /api/v1/accounts
if ($method === 'PATCH') {

    ['id' => $id, 'label' => $label, 'type' => $type, 'sold' => $sold] = checkRequiredArg($body, ['id', 'label', 'type', 'sold']);

    $account = new Account($id);
    $account->setLabel($label);
    $account->setType($type);

    $prev_sold = Operation::getLastOperationSoldByAccount($id, date('Y-m-d'));

    if ($sold - $prev_sold != 0) {
        Operation::createOperation("Balance update", date('Y-m-d'), $sold - $prev_sold, 6, 0, $id);
    }

    $account->update();

    sendAPIResponse(200, 'Account updated', []);
}

// DELETE /api/v1/accounts
if ($method === 'DELETE') {
    ['id' => $id] = checkRequiredArg($body, ['id']);

    Account::deleteAccount($id);
    sendAPIResponse(200, 'Account deleted', []);
}

sendAPIResponse(405, 'Method not allowed', []);
