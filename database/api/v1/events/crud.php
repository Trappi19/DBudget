<?php
header('Content-Type: application/json');
require($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');
require($_SERVER['DOCUMENT_ROOT'] . '/database/tables/regular_event.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/api/v1/validate.php');



// GET /api/v1/events?accounts=[...]&date=... -> liste
if ($method === 'GET') {
    $arg = json_decode($_GET['accounts'] ?? '[]');
    $date = sanitize_date(sanitize_body(['date' => $_GET['date'] ?? date('Y-m-d')])['date']);
    if ($date === false) {
        http_response_code(400);
        echo json_encode(['code' => 400, 'message' => 'Invalid date format (expected Y-m-d)', 'data' => []]);
        exit;
    }

    $futureDate = date('Y-m-d', strtotime('+1 year', strtotime($date)));

    $accountIDs = [];
    foreach ($arg as $value) {
        $id = sanitize_body(['id' => $value->id_account ?? null])['id'];
        if ($id >= 0) $accountIDs[] = $id;
    }

    if (empty($accountIDs)) {
        echo json_encode(['code' => 200, 'message' => 'OK', 'data' => []]);
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($accountIDs), '?'));

    $query = $db->prepare(
        'SELECT * FROM regular_event
         WHERE id_account IN (' . $placeholders . ')
         AND end >= ?
         AND start <= ?
         ORDER BY start ASC'
    );
    $query->execute(array_merge($accountIDs, [$date, $futureDate]));
    echo json_encode(['code' => 200, 'message' => 'OK', 'data' => $query->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

// POST /api/v1/events
if ($method === 'POST') {
    ['label' => $label, 'start' => $start, 'end' => $end, 'amount' => $amount, 'frequency' => $frequency, 'category' => $category, 'id_account' => $id_account] = checkRequiredArg($body, ['label', 'start', 'end', 'amount', 'frequency', 'category', 'id_account'], ['id_account']);

    $start = sanitize_date($start ?? '');
    $end   = sanitize_date($end ?? '');
    if ($start === false || $end === false) {
        http_response_code(400);
        echo json_encode(['code' => 400, 'message' => 'Invalid date format (expected Y-m-d)', 'data' => []]);
        exit;
    }

    RegularEvent::createRegularEvent($label, $start, $end, $amount, $frequency, $category, $id_account);

    http_response_code(201);
    echo json_encode(['code' => 201, 'message' => 'Event created', 'data' => []]);
    exit;
}

// PATCH /api/v1/events
if ($method === 'PATCH') {
    ['id' => $id, 'label' => $label, 'amount' => $amount, 'start' => $start, 'end' => $end, 'frequency' => $frequency, 'category' => $category] = checkRequiredArg($body, ['id', 'label', 'amount', 'start', 'end', 'frequency', 'category'], ['id']);

    $start = sanitize_date($start ?? '');
    $end   = sanitize_date($end ?? '');
    if ($start === false || $end === false) {
        http_response_code(400);
        echo json_encode(['code' => 400, 'message' => 'Invalid date format (expected Y-m-d)', 'data' => []]);
        exit;
    }

    $event = new RegularEvent($id);
    $event->setLabel($label);
    $event->setAmount($amount);
    $event->setStart($start);
    $event->setEnd($end);
    $event->setFrequencyType($frequency);
    $event->setCategory($category);
    $event->update();

    http_response_code(200);
    echo json_encode(['code' => 200, 'message' => 'Event updated', 'data' => []]);
    exit;
}

// DELETE /api/v1/events
if ($method === 'DELETE') {
    ['id' => $id] = checkRequiredArg($body, ['id'], ['id']);

    RegularEvent::deleteRegularEvent($id);

    http_response_code(200);
    echo json_encode(['code' => 200, 'message' => 'Event deleted', 'data' => []]);
    exit;
}

http_response_code(405);
echo json_encode(['code' => 405, 'message' => 'Method not allowed', 'data' => []]);
