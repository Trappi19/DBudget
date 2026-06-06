<?php
header('Content-Type: application/json');

require($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');
require($_SERVER['DOCUMENT_ROOT'] . '/database/tables/operation_type.php');

// GET /api/v1/operations/types
if (isset($_GET['type'])) {
    sendAPIResponse(200, 'OK', OperationType::getByAccountType((int)$_GET['type']));
}

sendAPIResponse(200, 'OK', OperationType::getAll());
