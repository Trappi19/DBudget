<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');

class Account
{
    private $id_account;
    private $label;
    private $type; // 0 = checking account, 1 = savings account
    private $user_email;
    private $icon; // Base64 encoded image string

    public function __construct($id_account)
    {
        global $db;

        $query = $db->prepare('SELECT * FROM bank_account WHERE id_account = :id_account');
        $query->execute(['id_account' => $id_account]);
        $result = $query->fetch();

        if (!$result) {
            http_response_code(404);
            echo json_encode(['code' => 404, 'message' => 'Account not found']);
            exit;
        }

        $this->id_account = $result['id_account'];
        $this->label = $result['label'];
        $this->type = $result['type'];
        $this->user_email = $result['user_email'];
        $this->icon = $result['icon'];
    }
    public function __destruct()
    {
        exit;
    }

    public function update()
    {
        global $db;

        $query = $db->prepare('UPDATE bank_account SET label = :label, type = :type, user_email = :user_email, icon = :icon WHERE id_account = :id_account');

        $query->execute([
            'id_account' => $this->id_account,
            'label' => $this->label,
            'type' => $this->type,
            'user_email' => $this->user_email,
            'icon' => $this->icon
        ]);
    }

    public function getId()
    {
        return $this->id_account;
    }
    public function getLabel()
    {
        return $this->label;
    }
    public function getType()
    {
        return $this->type;
    }
    public function getUserEmail()
    {
        return $this->user_email;
    }
    public function getProfilePicture()
    {
        return $this->icon;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }
    public function setType($type)
    {
        $this->type = $type;
    }
    public function setUserEmail($user_email)
    {
        $this->user_email = $user_email;
    }
    public function setProfilePicture($icon)
    {
        $this->icon = $icon;
    }

    public static function createAccount($label, $type, $user_email)
    {
        global $db;

        $query = $db->prepare('INSERT INTO bank_account (label, type, user_email) VALUES (:label, :type, :user_email)');
        $query->execute([
            'label' => $label,
            'type' => $type,
            'user_email' => $user_email
        ]);

        return $db->lastInsertId();
    }

    public static function deleteAccount($id_account)
    {
        global $db;

        $query = $db->prepare('DELETE FROM bank_account WHERE id_account = :id_account');
        $query->execute(['id_account' => $id_account]);

        // Also delete all operations on this account
        $query = $db->prepare('DELETE FROM operation WHERE id_account = :id_account');
        $query->execute(['id_account' => $id_account]);
    }

    public static function getAccountsByUser($email)
    {
        global $db;

        $query = $db->prepare('SELECT * FROM bank_account WHERE user_email = :user_email');
        $query->execute(['user_email' => $email]);
        $result = $query->fetchAll();

        return $result;
    }
}
