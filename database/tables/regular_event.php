<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/tables/operation.php');

class RegularEvent
{
    private $id_regular_event;
    private $label;
    private $start;
    private $end;
    private $amount;
    private $frequency_type; // 0 = every day, 1 = every week, 2 = every month, 3 = every year
    private $category; // 0 = Groceries, 1 = leisure, 2 = rent & utilities, 3 = health, 4 = Clothing & Needed, 5 = other
    private $id_account;

    public function __construct($id_regular_event)
    {
        global $db;

        $query = $db->prepare('SELECT * FROM regular_event WHERE id_regular_event = :id_regular_event');
        $query->execute(['id_regular_event' => $id_regular_event]);
        $result = $query->fetch();

        if (!$result) {
            http_response_code(404);
            echo json_encode(['code' => 404, 'message' => 'Event not found']);
            exit;
        }

        $this->id_regular_event = $result['id_regular_event'];
        $this->label = $result['label'];
        $this->start = $result['start'];
        $this->end = $result['end'];
        $this->amount = $result['amount'];
        $this->frequency_type = $result['frequency_type'];
        $this->category = $result['category'];
        $this->id_account = $result['id_account'];
    }
    public function __destruct()
    {
        exit;
    }

    public function update()
    {
        global $db;

        $query = $db->prepare('UPDATE regular_event SET label = :label, start = :start, end = :end, amount = :amount, frequency_type = :frequency_type, category = :category, id_account = :id_account WHERE id_regular_event = :id_regular_event');

        $query->execute([
            'id_regular_event' => $this->id_regular_event,
            'label' => $this->label,
            'start' => $this->start,
            'end' => $this->end,
            'amount' => $this->amount,
            'frequency_type' => $this->frequency_type,
            'category' => $this->category,
            'id_account' => $this->id_account
        ]);

        RegularEvent::updateOperationsFromRegularEvent($this->id_regular_event, $this->label, $this->start, $this->end, $this->amount, $this->frequency_type, $this->category, $this->id_account);
    }

    public function getId()
    {
        return $this->id_regular_event;
    }
    public function getLabel()
    {
        return $this->label;
    }
    public function getStart()
    {
        return $this->start;
    }
    public function getEnd()
    {
        return $this->end;
    }
    public function getAmount()
    {
        return $this->amount;
    }
    public function getFrequencyType()
    {
        return $this->frequency_type;
    }
    public function getCategory()
    {
        return $this->category;
    }
    public function getIdAccount()
    {
        return $this->id_account;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }
    public function setStart($start)
    {
        $this->start = $start;
    }
    public function setEnd($end)
    {
        $this->end = $end;
    }
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }
    public function setFrequencyType($frequency_type)
    {
        $this->frequency_type = $frequency_type;
    }
    public function setCategory($category)
    {
        $this->category = $category;
    }
    public function setIdAccount($id_account)
    {
        $this->id_account = $id_account;
    }

    public static function createRegularEvent($label, $start, $end, $amount, $frequency_type, $category, $id_account)
    {
        global $db;

        $query = $db->prepare('INSERT INTO regular_event (label, start, end, amount, frequency_type, category, id_account) VALUES (:label, :start, :end, :amount, :frequency_type, :category, :id_account)');

        $query->execute([
            'label' => $label,
            'start' => $start,
            'end' => $end,
            'amount' => $amount,
            'frequency_type' => $frequency_type,
            'category' => $category,
            'id_account' => $id_account
        ]);

        RegularEvent::updateOperationsFromRegularEvent($db->lastInsertId(), $label, $start, $end, $amount, $frequency_type, $category, $id_account);
    }

    public static function deleteRegularEvent($id_regular_event)
    {
        global $db;

        $query = $db->prepare('DELETE FROM regular_event WHERE id_regular_event = :id_regular_event');
        $query->execute(['id_regular_event' => $id_regular_event]);
        
        RegularEvent::deleteOperationsFromRegularEvent($id_regular_event);
    }

    public static function updateOperationsFromRegularEvent($id_regular_event, $label, $start, $end, $amount, $frequency_type, $category, $id_account)
    {
        RegularEvent::deleteOperationsFromRegularEvent($id_regular_event);

        $date_index = $start;
        while ($date_index <= $end) {
            Operation::createOperation($label, $date_index, $amount, $category, $id_regular_event, $id_account);
            $date_index = date('Y-m-d', strtotime($date_index . (($frequency_type == 0) ? ' + 1 day' : (($frequency_type == 1) ? ' + 1 week' : (($frequency_type == 2) ? ' + 1 month' : ' + 1 year')))));
        }
    }

    public static function deleteOperationsFromRegularEvent($id_regular_event)
    {
        global $db;
        
        $query = $db->prepare('SELECT id_operation, date, amount, id_account FROM operation WHERE regularity = :id_regular_event');
        $query->execute(['id_regular_event' => $id_regular_event]);
        $result = $query->fetchAll();

        foreach ($result as $operation) {
            $query = $db->prepare('DELETE FROM operation WHERE id_operation = :id_operation');
            $query->execute(['id_operation' => $operation["id_operation"]]);

            Operation::updateFurtherOperationSold($operation["date"], -$operation["amount"], $operation["id_account"]);
        }
    }

    public static function getActiveRegularEvents($date, $id_account)
    {
        global $db;

        $query = $db->prepare('SELECT * FROM regular_event WHERE id_account = :id_account AND end >= :date');
        $query->execute(['date' => $date, 'id_account' => $id_account]);
        $result = $query->fetchAll();

        return $result;
    }
}
