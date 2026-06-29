<?php

if ($method !== 'POST') {
    sendAPIResponse(405, 'Method not allowed', []);
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/tables/user.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/tables/pending_account.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/mailer.php');

checkRequiredArg($body, ['email', 'username', 'password', 'lang']);

// The gateway already sanitized the body (trim + htmlspecialchars). The
// password must be hashed from its RAW value to stay consistent with login,
// so decode the relevant fields back.
$email        = htmlspecialchars_decode($body['email'], ENT_QUOTES);
$usernameRaw  = htmlspecialchars_decode($body['username'], ENT_QUOTES);
$password     = htmlspecialchars_decode($body['password'], ENT_QUOTES);
$lang         = htmlspecialchars_decode($body['lang'], ENT_QUOTES);

// The encoded username is what gets persisted (consistent with settings).
$usernameStored = $body['username'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendAPIResponse(422, 'Invalid email', []);
}

$allowedLangs = array_map(
    fn($f) => basename($f, '.json'),
    glob($_SERVER['DOCUMENT_ROOT'] . '/lang/*.json')
);
if (!in_array($lang, $allowedLangs, true)) {
    $lang = 'English';
}

// An account with this email must not already exist.
if (User::exists($email)) {
    sendAPIResponse(409, 'Email already used', []);
}

// Generate an 8-char alphanumeric verification code, store only its hash.
$code      = generate_verification_code(8);
$codeHash  = password_hash($code, PASSWORD_DEFAULT);
$expiresAt = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');

// Hash the password now; persist all the pending account data.
$cred = User::createPassword($password);

try {
    PendingAccount::save($email, $usernameStored, $cred['hash'], $cred['salt'], $lang, $codeHash, $expiresAt);
} catch (Throwable $e) {
    error_log('[Account] Pending store failed: ' . $e->getMessage());
    sendAPIResponse(500, 'Could not create account', []);
}

$mailSender   = getenv('MAIL_SENDER');
$mailPassword = getenv('MAIL_SENDER_PASSWORD');

if (!$mailSender || !$mailPassword) {
    error_log('[Account] Mail configuration missing (MAIL_SENDER / MAIL_SENDER_PASSWORD)');
    sendAPIResponse(500, 'Mail configuration missing', []);
}

$subject  = '[Dbudget] code - Code de vérification';
$htmlBody = buildVerificationMailHtml($usernameRaw, $code);
$textBody = buildVerificationMailText($usernameRaw, $code);

try {
    $mailer = new Mailer('smtp.gmail.com', 587, $mailSender, $mailPassword);
    $mailer->send($mailSender, 'DBudget Team', $email, $subject, $htmlBody, $textBody);
} catch (RuntimeException $e) {
    error_log('[Account] SMTP error: ' . $e->getMessage());
    sendAPIResponse(500, 'Mail delivery failed', []);
}

sendAPIResponse(201, 'Verification code sent', []);

// ─────────────────────────────────────────────────────────────────────────────

/**
 * Cryptographically secure 8-char code made of digits and letters.
 */
function generate_verification_code(int $length = 8): string
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $max      = strlen($alphabet) - 1;
    $code     = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $alphabet[random_int(0, $max)];
    }
    return $code;
}

function buildVerificationMailHtml(string $username, string $code): string
{
    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $code     = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');

    return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DBudget — Code de vérification</title>
<style>
  body       { margin:0; padding:0; background:#e7e7e7; font-family:"lato",Arial,Helvetica,sans-serif; color:#333; }
  .wrapper   { max-width:560px; margin:32px auto; padding:32px 40px; background:#fff; border-radius:10px; box-shadow:0 0 9px 0 rgba(0,0,0,.1); }
  .title     { margin:0 0 24px; font-size:20px; font-weight:700; color:#333; }
  .greeting  { font-size:15px; line-height:1.7; color:#333; margin:0 0 8px; }
  .code-box  { margin:24px 0; padding:18px 20px; background:#f7f7f7; border-radius:8px; text-align:center;
               font-size:30px; font-weight:700; letter-spacing:6px; color:#2c7be5; }
  .validity  { font-size:14px; color:#888; margin:0 0 24px; }
  .signature { font-size:15px; line-height:1.7; color:#333; margin:24px 0 0; }
  .footer    { margin-top:28px; padding-top:16px; border-top:1px solid #eee; font-size:12px; color:#bbb; }
</style>
</head>
<body>
<div class="wrapper">
  <h1 class="title">DBudget</h1>

  <p class="greeting">Bonjour {$username},</p>
  <p class="greeting">Votre code de vérification est :</p>

  <div class="code-box">{$code}</div>

  <p class="validity">Le code est valide 15 minutes.</p>

  <p class="signature">Cordialement,<br>DBudget Team</p>

  <div class="footer">Ce mail est automatique, ne pas répondre.</div>
</div>
</body>
</html>
HTML;
}

function buildVerificationMailText(string $username, string $code): string
{
    return "Bonjour {$username},\r\n"
        . "Votre code de vérification est :\r\n"
        . "{$code}\r\n"
        . "Le code est valide 15 minutes.\r\n"
        . "\r\n"
        . "Cordialement,\r\n"
        . "DBudget Team\r\n"
        . "\r\n"
        . "Ce mail est automatique, ne pas répondre\r\n";
}
