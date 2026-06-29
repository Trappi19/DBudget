<?php

if ($method !== 'POST') {
    sendAPIResponse(405, 'Method not allowed', []);
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/tables/user.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/tables/pending_account.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/mailer.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/lang.php');

checkRequiredArg($body, ['email', 'username', 'password', 'lang']);

// gateway alreay encode the body, so we need to decode it before storing it in the database.
$email = htmlspecialchars_decode($body['email'], ENT_QUOTES);
$usernameRaw = htmlspecialchars_decode($body['username'], ENT_QUOTES);
$password = htmlspecialchars_decode($body['password'], ENT_QUOTES);
$lang = htmlspecialchars_decode($body['lang'], ENT_QUOTES);


$usernameStored = $body['username'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendAPIResponse(422, 'Invalid email', []);
}

if (!in_array($lang, get_available_language_codes(), true)) {
    $lang = 'English';
}

if (User::exists($email)) {
    sendAPIResponse(409, 'Email already used', []);
}

// Generate the verification code (stored hashed) and its expiry window
$code = generate_verification_code(8);
$codeHash = password_hash($code, PASSWORD_DEFAULT);
$expiresAt = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');

$cred = User::createPassword($password);

// Opportunistic sweep: clear out pending accounts whose code already expired,
PendingAccount::deleteExpired();

try {
    // REPLACE overwrites any existing pending account for this email:
    // re-registering simply resets the verification code and attempt counter.
    PendingAccount::save($email, $usernameStored, $cred['hash'], $cred['salt'], $lang, $codeHash, $expiresAt);
} catch (Throwable $e) {
    error_log('[Account] Pending store failed: ' . $e->getMessage());
    sendAPIResponse(500, 'Could not create account', []);
}

$mailSender = getenv('MAIL_SENDER');
$mailPassword = getenv('MAIL_SENDER_PASSWORD');

if (!$mailSender || !$mailPassword) {
    error_log('[Account] Mail configuration missing (MAIL_SENDER / MAIL_SENDER_PASSWORD)');
    sendAPIResponse(500, 'Mail configuration missing', []);
}

$subject = trans_locale($lang, 'auth.register.verification_email.subject');
$htmlBody = buildVerificationMailHtml($usernameRaw, $code, $lang);
$textBody = buildVerificationMailText($usernameRaw, $code, $lang);

try {
    $mailer = new Mailer('smtp.gmail.com', 587, $mailSender, $mailPassword);
    $mailer->send('DBudget Team', $email, $subject, $htmlBody, $textBody);
} catch (RuntimeException $e) {
    error_log('[Account] SMTP error: ' . $e->getMessage());
    sendAPIResponse(500, 'Mail delivery failed', []);
}

sendAPIResponse(201, 'Verification code sent', []);

// ─────────────────────────────────────────────────────────────────────────────

function generate_verification_code(int $length = 8): string
{
    // random_bytes() draws from the OS CSPRNG (unpredictable), bin2hex encodes
    // each byte as 2 hex chars. ceil() covers odd lengths, substr trims to size.
    return substr(bin2hex(random_bytes((int)ceil($length / 2))), 0, $length);
}

function buildVerificationMailHtml(string $username, string $code, string $lang): string
{
    $greeting = htmlspecialchars(str_replace('{username}', $username, trans_locale($lang, 'auth.register.verification_email.greeting')), ENT_QUOTES, 'UTF-8');
    $intro = htmlspecialchars(trans_locale($lang, 'auth.register.verification_email.intro'), ENT_QUOTES, 'UTF-8');
    $validity = htmlspecialchars(trans_locale($lang, 'auth.register.verification_email.validity'), ENT_QUOTES, 'UTF-8');
    $signature = htmlspecialchars(trans_locale($lang, 'auth.register.verification_email.signature'), ENT_QUOTES, 'UTF-8');
    $footer = htmlspecialchars(trans_locale($lang, 'auth.register.verification_email.footer'), ENT_QUOTES, 'UTF-8');
    $subject = htmlspecialchars(trans_locale($lang, 'auth.register.verification_email.subject'), ENT_QUOTES, 'UTF-8');
    $code = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');

    // BCP47 tag (e.g. "en-US") -> short language subtag ("en") for the <html lang> attribute.
    $htmlLang = htmlspecialchars(explode('-', trans_locale($lang, '_locale'))[0], ENT_QUOTES, 'UTF-8');

    return <<<HTML
<!DOCTYPE html>
<html lang="{$htmlLang}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DBudget — {$subject}</title>
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

  <p class="greeting">{$greeting}</p>
  <p class="greeting">{$intro}</p>

  <div class="code-box">{$code}</div>

  <p class="validity">{$validity}</p>

  <p class="signature">{$signature}<br>DBudget Team</p>

  <div class="footer">{$footer}</div>
</div>
</body>
</html>
HTML;
}

function buildVerificationMailText(string $username, string $code, string $lang): string
{
    $greeting = str_replace('{username}', $username, trans_locale($lang, 'auth.register.verification_email.greeting'));
    $intro = trans_locale($lang, 'auth.register.verification_email.intro');
    $validity = trans_locale($lang, 'auth.register.verification_email.validity');
    $signature = trans_locale($lang, 'auth.register.verification_email.signature');
    $footer = trans_locale($lang, 'auth.register.verification_email.footer');

    return "{$greeting}\r\n"
        . "{$intro}\r\n"
        . "{$code}\r\n"
        . "{$validity}\r\n"
        . "\r\n"
        . "{$signature}\r\n"
        . "DBudget Team\r\n"
        . "\r\n"
        . "{$footer}\r\n";
}
