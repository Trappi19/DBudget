<?php

header('Content-Type: application/json');
require($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');
require($_SERVER['DOCUMENT_ROOT'] . '/database/tables/regular_event.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/api/v1/apiUtils.php');



// GET /api/v1/events?accounts=[...]&date=... -> liste
if ($method === 'GET') {

    $date = sanitize_date($_GET['date'] ?? date('Y-m-d'));
    $futureDate = date('Y-m-d', strtotime('+1 year', strtotime($date)));
    $accounts = json_decode($_GET['accounts'] ?? '[]');
    $limit = isset($_GET['limit']) ? ' LIMIT ' . (int)$_GET['limit'] : '';

    if (empty($accounts)) {
        sendAPIResponse(400, 'No account provided', []);
    }
    foreach ($accounts as $account) {
        $account = sanitize_int($account ?? -1);
    }

    $placeholders = implode(',', array_fill(0, count($accounts), '?'));

    $query = $db->prepare(
        'SELECT * FROM regular_event
         WHERE id_account IN (' . $placeholders . ')
         AND end >= ?
         AND start <= ?
         ORDER BY start ASC'
    );
    $query->execute(array_merge($accounts, [$date, $futureDate]));
    sendAPIResponse(200, 'OK', $query->fetchAll(PDO::FETCH_ASSOC));
}

// POST /api/v1/events
if ($method === 'POST') {
    ['label' => $label, 'start' => $start, 'end' => $end, 'amount' => $amount, 'frequency' => $frequency, 'category' => $category, 'id_account' => $id_account] = checkRequiredArg($body, ['label', 'start', 'end', 'amount', 'frequency', 'category', 'id_account']);

    $start = sanitize_date($start ?? '');
    $end = sanitize_date($end ?? '');

    RegularEvent::createRegularEvent($label, $start, $end, $amount, $frequency, $category, $id_account);

    sendAPIResponse(201, 'Event created', []);
}

// PATCH /api/v1/events
if ($method === 'PATCH') {
    ['id' => $id, 'label' => $label, 'amount' => $amount, 'start' => $start, 'end' => $end, 'frequency' => $frequency, 'category' => $category] = checkRequiredArg($body, ['id', 'label', 'amount', 'start', 'end', 'frequency', 'category']);

    $start = sanitize_date($start ?? '');
    $end = sanitize_date($end ?? '');

    $event = new RegularEvent($id);
    $event->setLabel($label);
    $event->setAmount($amount);
    $event->setStart($start);
    $event->setEnd($end);
    $event->setFrequencyType($frequency);
    $event->setCategory($category);
    $event->update();

    sendAPIResponse(200, 'Event updated', []);
}

// DELETE /api/v1/events
if ($method === 'DELETE') {
    ['id' => $id] = checkRequiredArg($body, ['id']);

    RegularEvent::deleteRegularEvent($id);

    sendAPIResponse(200, 'Event deleted', []);
}

sendAPIResponse(405, 'Method not allowed', []);
