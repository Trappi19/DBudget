<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');

class PendingAccount
{
    public static function save(
        string $email,
        string $username,
        string $passwordHash,
        string $salt,
        string $lang,
        string $codeHash,
        string $expiresAt
    ): void
    {
        global $db;

        // INSERT ... ON DUPLICATE KEY UPDATE (not REPLACE): on a re-registration
        // the existing row is updated in place, so created_at keeps its original
        // value. REPLACE would DELETE + INSERT and reset created_at to now.
        $query = $db->prepare(
            'INSERT INTO pending_account
                (email, username, password, salt, lang, code_hash, attempts, expires_at)
             VALUES
                (:email, :username, :password, :salt, :lang, :code_hash, 0, :expires_at)
             ON DUPLICATE KEY UPDATE
                username   = VALUES(username),
                password   = VALUES(password),
                salt       = VALUES(salt),
                lang       = VALUES(lang),
                code_hash  = VALUES(code_hash),
                attempts   = 0,
                expires_at = VALUES(expires_at)'
        );

        $query->execute([
            'email'      => $email,
            'username'   => $username,
            'password'   => $passwordHash,
            'salt'       => $salt,
            'lang'       => $lang,
            'code_hash'  => $codeHash,
            'expires_at' => $expiresAt,
        ]);
    }

    /** @return array|null */
    public static function find(string $email): ?array
    {
        global $db;

        $query = $db->prepare('SELECT * FROM pending_account WHERE email = :email');
        $query->execute(['email' => $email]);
        $result = $query->fetch();

        return $result ?: null;
    }

    public static function incrementAttempts(string $email): void
    {
        global $db;

        $query = $db->prepare('UPDATE pending_account SET attempts = attempts + 1 WHERE email = :email');
        $query->execute(['email' => $email]);
    }

    public static function delete(string $email): void
    {
        global $db;

        $query = $db->prepare('DELETE FROM pending_account WHERE email = :email');
        $query->execute(['email' => $email]);
    }

    public static function deleteExpired(): void
    {
        global $db;

        $query = $db->prepare('DELETE FROM pending_account WHERE expires_at < NOW()');
        $query->execute();
    }
}
