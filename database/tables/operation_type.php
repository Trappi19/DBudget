<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');

class OperationType
{
    public static function getAll()
    {
        global $db;

        $query = $db->prepare('SELECT * FROM operation_type');
        $query->execute();
        $result = $query->fetchAll();

        return $result;
    }

    public static function getByAccountType($account_type) // 0 = checking account, 1 = savings account
    {
        global $db;

        $query = $db->prepare('SELECT * FROM operation_type WHERE account_type = :account_type OR account_type = -1');
        $query->execute(['account_type' => $account_type]);
        $result = $query->fetchAll();

        return $result;
    }
}
