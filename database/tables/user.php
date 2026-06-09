<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');

class User
{
    private $email;
    private $username;
    private $password;
    private $salt;
    private $lang;

    public function __construct($email)
    {
        global $db;

        $query = $db->prepare('SELECT * FROM user WHERE email = :email');
        $query->execute(['email' => $email]);
        $result = $query->fetch();

        if (!$result) {
            sendAPIResponse(404, 'User not found', []);
        }

        $this->email = $result['email'];
        $this->username = $result['username'];
        $this->password = $result['password'];
        $this->salt = $result['salt'];
        $this->lang = $result['lang'] ?? 'English';
    }
    public function __destruct()
    {
        exit;
    }

    public function update()
    {
        global $db;

        $query = $db->prepare('UPDATE user SET username = :username, password = :password, salt = :salt, lang = :lang WHERE email = :email');

        $query->execute([
            'email' => $this->email,
            'username' => $this->username,
            'password' => $this->password,
            'salt' => $this->salt,
            'lang' => $this->lang
        ]);
    }

    public function getUsername()
    {
        return $this->username;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getPassword()
    {
        return $this->password;
    }
    public function getSalt()
    {
        return $this->salt;
    }
    public function setUsername($string)
    {
        $this->username = $string;
    }
    public function setPassword($hached_password)
    {
        $this->password = $hached_password;
    }
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }
    public function getLang()
    {
        return $this->lang;
    }
    public function setLang($lang)
    {
        $allowed = array_map(
            fn($f) => basename($f, '.json'),
            glob($_SERVER['DOCUMENT_ROOT'] . '/lang/*.json')
        );
        $this->lang = in_array($lang, $allowed) ? $lang : 'English';
    }

    public static function createPassword($rawPassword)
    {
        $salt = bin2hex(random_bytes(16));
        $hash = hash_pbkdf2("sha512", $rawPassword, $salt, 1000, 64);
        return array("salt" => $salt, "hash" => $hash);
    }


    public static function checkPassword($rawPassword, $salt, $hash)
    {
        $checkHash = hash_pbkdf2("sha512", $rawPassword, $salt, 1000, 64);
        return $checkHash == $hash;
    }


    public static function checkLogin($email, $password)
    {
        global $db;

        $query = $db->prepare('SELECT * FROM user WHERE email = :email');
        $query->execute(['email' => $email]);
        $result = $query->fetch();

        if (!$result) { // If account doesn't exist
            return false;
        }

        $salt = $result['salt'];
        $hash = $result['password'];

        if (is_null($hash)) { // If account has no password, set it
            $user = new User($email);

            $new_password = User::createPassword($password);
            $user->setPassword($new_password['hash']);
            $user->setSalt($new_password['salt']);
            $user->update();

            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
            $title = "Login";
            $error = 2;
            require $_SERVER['DOCUMENT_ROOT'] . '/public/templates/login.php';
        }

        return self::checkPassword($password, $salt, $hash);
    }
}
