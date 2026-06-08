<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');

class Operation
{
    private $id_operation;
    private $label;
    private $date;
    private $amount;
    private $category; // 0 = saving, 1 = Groceries, 2 = leisure, 3 = rent & utilities, 4 = health, 5 = Clothing & Needed, 6 = other, 7 = withdrawal, 8 = Interest
    private $regularity; // 0 = one time, > = regular
    private $new_sold;
    private $id_account;

    public function __construct($id_operation)
    {
        global $db;

        $query = $db->prepare('SELECT * FROM operation WHERE id_operation = :id_operation');
        $query->execute(['id_operation' => $id_operation]);
        $result = $query->fetch();

        if (!$result) {
            sendAPIResponse(404, 'Operation not found', []);
        }

        $this->id_operation = $result['id_operation'];
        $this->label = $result['label'];
        $this->date = $result['date'];
        $this->amount = $result['amount'];
        $this->category = $result['category'];
        $this->regularity = $result['regularity'];
        $this->new_sold = $result['new_sold'];
        $this->id_account = $result['id_account'];
    }
    public function __destruct()
    {
        exit;
    }

    public function update()
    {
        global $db;

        $old_operation = new Operation($this->id_operation);
        $diff_amount = $this->amount - $old_operation->getAmount();

        Operation::updateFurtherOperationSold($this->date, $diff_amount, $this->id_account);

        $query = $db->prepare('UPDATE operation SET label = :label, date = :date, amount = :amount, category = :category, regularity = :regularity, new_sold = :new_sold, id_account = :id_account WHERE id_operation = :id_operation');

        $query->execute([
            'id_operation' => $this->id_operation,
            'label' => $this->label,
            'date' => $this->date,
            'amount' => $this->amount,
            'category' => $this->category,
            'regularity' => $this->regularity,
            'new_sold' => $this->new_sold + $diff_amount,
            'id_account' => $this->id_account
        ]);
    }

    public function getId()
    {
        return $this->id_operation;
    }
    public function getLabel()
    {
        return $this->label;
    }
    public function getDate()
    {
        return $this->date;
    }
    public function getAmount()
    {
        return $this->amount;
    }
    public function getCategory()
    {
        return $this->category;
    }
    public function getRegularity()
    {
        return $this->regularity;
    }
    public function getNewSold()
    {
        return $this->new_sold;
    }
    public function getIdAccount()
    {
        return $this->id_account;
    }
    public function setLabel($label)
    {
        $this->label = $label;
    }
    public function setDate($date)
    {
        $this->date = $date;
    }
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }
    public function setCategory($category)
    {
        $this->category = $category;
    }
    public function setRegularity($regularity)
    {
        $this->regularity = $regularity;
    }
    public function setNewSold($new_sold)
    {
        $this->new_sold = $new_sold;
    }
    public function setIdAccount($id_account)
    {
        $this->id_account = $id_account;
    }

    public static function createOperation($label, $date, $amount, $category, $regularity, $id_account)
    {
        global $db;

        $new_sold = Operation::getLastOperationSoldByAccount($id_account, $date) + $amount;
        Operation::updateFurtherOperationSold($date, $amount, $id_account);

        $query = $db->prepare('INSERT INTO operation (label, date, amount, category, regularity, new_sold, id_account) VALUES (:label, :date, :amount, :category, :regularity, :new_sold, :id_account)');

        $query->execute([
            'label' => $label,
            'date' => $date,
            'amount' => $amount,
            'category' => $category,
            'regularity' => $regularity,
            'new_sold' => $new_sold,
            'id_account' => $id_account
        ]);
    }

    public static function updateFurtherOperationSold($date, $amount, $id_account)
    {
        global $db;
        $query = $db->prepare('UPDATE operation SET new_sold = new_sold + :amount WHERE date >= :date AND id_account = :id_account');

        $query->execute([
            'date' => $date,
            'amount' => $amount,
            'id_account' => $id_account
        ]);
    }

    public static function deleteOperation($id_operation)
    {
        global $db;
        $operation = new Operation($id_operation);

        $query = $db->prepare('DELETE FROM operation WHERE id_operation = :id_operation');
        $query->execute(['id_operation' => $id_operation]);

        Operation::updateFurtherOperationSold($operation->getDate(), -$operation->getAmount(), $operation->getIdAccount());
    }

    public static function getOperationsByAccount($id_account)
    {
        global $db;

        $query = $db->prepare('SELECT * FROM operation WHERE id_account = :id_account');
        $query->execute(['id_account' => $id_account]);
        $result = $query->fetchAll();

        return $result;
    }

    public static function getLastOperationSoldByAccount($id_account, $date)
    {
        global $db;

        $query = $db->prepare('SELECT new_sold FROM operation WHERE id_account = :id_account AND date <= :date ORDER BY date DESC LIMIT 1');
        $query->execute(['id_account' => $id_account, 'date' => $date]);
        $result = $query->fetch();

        if (!$result) {
            return 0;
        }

        return $result['new_sold'];
    }
}
