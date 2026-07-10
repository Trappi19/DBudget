<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/api/v1/apiUtils.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/lang.php');

/**
 * Stateless authentication backed by a signed JWT stored in an httpOnly cookie.
 *
 * Token claims:
 *   sub   int     user id (id_user)
 *   email string  user email (stable, used for account-scoped queries)
 *   lvl   int     account_level (0 = user, 1 = super user)
 *   acc   int[]   ids of the bank accounts the user owns
 *   iat   int     issued-at timestamp
 *   exp   int     expiry timestamp
 *
 * username and language are intentionally NOT in the token: they are mutable
 * preferences and are read from the database per request (see lang.php).
 */
class Auth
{
    private const COOKIE_NAME = 'auth_token';
    private const ALGO        = 'HS256';
    private const TTL         = 604800; // 7 days, in seconds

    /** @var array|null Decoded claims for the current request (cached). */
    private static ?array $claims = null;
    private static bool $verified = false;

    private static function secret(): string
    {
        $secret = getenv('JWT_SECRET');
        if ($secret === false || $secret === '') {
            error_log('[Auth] JWT_SECRET is not set');
            http_response_code(500);
            exit('Server authentication is misconfigured.');
        }
        return $secret;
    }

    /**
     * Verify the request cookie and return the claims, or null when the caller
     * is not authenticated (missing / invalid / expired token). Cached.
     */
    public static function verify(): ?array
    {
        if (self::$verified) {
            return self::$claims;
        }
        self::$verified = true;

        $token = $_COOKIE[self::COOKIE_NAME] ?? '';
        if ($token === '') {
            return self::$claims = null;
        }

        try {
            $decoded = (array) JWT::decode($token, new Key(self::secret(), self::ALGO));
            $decoded['acc'] = array_map('intval', (array) ($decoded['acc'] ?? []));
            self::$claims = $decoded;
        } catch (\Throwable $e) {
            self::$claims = null;
        }

        return self::$claims;
    }

    public static function isAuthenticated(): bool
    {
        return self::verify() !== null;
    }

    /** Build claims from the database for $email and set the cookie. */
    public static function issueForUser(string $email): void
    {
        global $db;

        $query = $db->prepare('SELECT id_user, account_level FROM user WHERE email = :email');
        $query->execute(['email' => $email]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            sendAPIResponse(404, 'User not found', []);
        }

        $query = $db->prepare('SELECT id_account FROM bank_account WHERE user_email = :email');
        $query->execute(['email' => $email]);
        $accountIds = array_map('intval', $query->fetchAll(PDO::FETCH_COLUMN));

        $now = time();
        $claims = [
            'sub'   => (int) $user['id_user'],
            'email' => $email,
            'lvl'   => (int) $user['account_level'],
            'acc'   => $accountIds,
            'iat'   => $now,
            'exp'   => $now + self::TTL,
        ];

        self::writeCookie(JWT::encode($claims, self::secret(), self::ALGO), $claims['exp']);

        // Keep the current-request cache in sync so accessors see fresh data.
        self::$claims   = $claims;
        self::$verified = true;
    }

    /** Re-issue the cookie for the current user (e.g. after the account list changes). */
    public static function refresh(): void
    {
        $email = self::email();
        if ($email !== null) {
            self::issueForUser($email);
        }
    }

    /** Clear the authentication cookie (logout). */
    public static function clear(): void
    {
        setcookie(self::COOKIE_NAME, '', self::cookieOptions(time() - 3600));
        unset($_COOKIE[self::COOKIE_NAME]);
        self::$claims   = null;
        self::$verified = true;
    }

    public static function userId(): ?int
    {
        $c = self::verify();
        return $c !== null ? (int) $c['sub'] : null;
    }

    public static function email(): ?string
    {
        $c = self::verify();
        return $c['email'] ?? null;
    }

    public static function level(): int
    {
        $c = self::verify();
        return (int) ($c['lvl'] ?? 0);
    }

    /** @return int[] */
    public static function accountIds(): array
    {
        $c = self::verify();
        return $c['acc'] ?? [];
    }

    /**
     * True when the current user owns account $id. Checks the signed token
     * first, then falls back to the database (the token may be stale for an
     * account created within the same session before a refresh).
     */
    public static function ownsAccount(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        if (in_array($id, self::accountIds(), true)) {
            return true;
        }

        $email = self::email();
        if ($email === null) {
            return false;
        }

        global $db;
        $query = $db->prepare('SELECT 1 FROM bank_account WHERE id_account = :id AND user_email = :email');
        $query->execute(['id' => $id, 'email' => $email]);
        return (bool) $query->fetch();
    }

    private static function writeCookie(string $jwt, int $expires): void
    {
        setcookie(self::COOKIE_NAME, $jwt, self::cookieOptions($expires));
        // Make the token readable within the request that just issued it.
        $_COOKIE[self::COOKIE_NAME] = $jwt;
    }

    private static function cookieOptions(int $expires): array
    {
        return [
            'expires'  => $expires,
            'path'     => '/',
            'httponly' => true,
            'secure'   => !empty($_SERVER['HTTPS']),
            'samesite' => 'Lax',
        ];
    }
}

/**
 * Resolve a safe post-login redirect target. Only local /app/ paths are
 * accepted (prevents open redirects); login/logout are excluded to avoid loops.
 */
function login_redirect_target(): string
{
    $redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? '';

    if (is_string($redirect)
        && str_starts_with($redirect, '/app/')
        && !str_contains($redirect, '//')
        && !str_contains($redirect, '\\')
        && !str_starts_with($redirect, '/app/login')
        && !str_starts_with($redirect, '/app/logout')
    ) {
        return $redirect;
    }

    return '/app/home';
}

function requireLogin(): void
{
    if (!Auth::isAuthenticated()) {
        $target = $_SERVER['REQUEST_URI'] ?? '/app/home';
        header('Location: /app/login?redirect=' . urlencode($target));
        exit();
    }
}

function requireLoginApi(): void
{
    if (!Auth::isAuthenticated()) {
        sendAPIResponse(401, 'Not logged in', []);
    }
}
