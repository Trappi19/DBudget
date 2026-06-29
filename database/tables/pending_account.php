<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');

/**
 * Temporary store for pending account registrations.
 *
 * A pending row keeps the (already hashed) account credentials together with a
 * HASHED verification code and an expiry. It is overwritten when a new code is
 * requested for the same email, and deleted once the account is confirmed.
 */
class PendingAccount
{
    /**
     * Insert or replace the pending registration for an email.
     */
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

        $query = $db->prepare(
            'REPLACE INTO pending_account
                (email, username, password, salt, lang, code_hash, attempts, expires_at)
             VALUES
                (:email, :username, :password, :salt, :lang, :code_hash, 0, :expires_at)'
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

    /**
     * @return array|null The pending row, or null if none exists.
     */
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
}
