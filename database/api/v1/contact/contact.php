<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/database/connexion.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/database/tables/user.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/mailer.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/controler/helpers/lang.php');

if ($method !== 'POST') {
    sendAPIResponse(405, 'Method not allowed', []);
}

checkRequiredArg($body, ['theme', 'subject', 'message']);

// The gateway already escaped the body; decode back to raw text so each output
// format can re-encode it itself.
$theme = htmlspecialchars_decode($body['theme'], ENT_QUOTES);
$subject = htmlspecialchars_decode($body['subject'], ENT_QUOTES);
$message = htmlspecialchars_decode($body['message'], ENT_QUOTES);

$allowedThemes = ['Bug', 'Suggestion', 'Question', 'Autre'];
if (!in_array($theme, $allowedThemes, true)) {
    sendAPIResponse(422, 'Invalid theme', []);
}

if (mb_strlen($subject) > 70) {
    sendAPIResponse(422, 'Subject too long', []);
}
if (mb_strlen($message) > 10000) {
    sendAPIResponse(422, 'Message too long', []);
}

$userEmail = $_SESSION['email'];
$user = new User($userEmail);

// Coerce false (unset var) to '' so a missing config fails through the generic
// SMTP error path below rather than leaking its state to the user.
$mailContact = getenv('MAIL_CONTACT') ?: '';
$mailSender = getenv('MAIL_SENDER') ?: '';
$mailPassword = getenv('MAIL_SENDER_PASSWORD') ?: '';

$headerSafe = fn(string $s): string => str_replace(["\r", "\n"], ' ', $s);
$emailSubject = "[DBuget] " . $headerSafe($theme) . " - " . $headerSafe($subject);
$sentAt = date('d/m/Y H:i:s');

$username = htmlspecialchars_decode($user->getUsername(), ENT_QUOTES);
$level = $user->getAccountLevel();

$htmlBody = buildContactMailHtml($username, $userEmail, $level, $message, $sentAt);
$textBody = buildContactMailText($username, $userEmail, $level, $message, $sentAt);

try {
    $mailer = new Mailer('smtp.gmail.com', 587, $mailSender, $mailPassword);
    $mailer->send('DBuget Contact', $mailContact, $emailSubject, $htmlBody, $textBody);
} catch (RuntimeException $e) {
    error_log('[Contact] SMTP error: ' . $e->getMessage());
    sendAPIResponse(500, 'Mail delivery failed', []);
}

try {
    // Acknowledge in the language the user picked (trans() reads $_SESSION['lang']).
    $confirmSubject = $headerSafe(trans('settings.contact_form.confirmation.subject'));
    $confirmHtml = buildConfirmationMailHtml($username, $theme, $subject, $message, $sentAt);
    $confirmText = buildConfirmationMailText($username, $theme, $subject, $message, $sentAt);
    $mailer->send('DBuget', $userEmail, $confirmSubject, $confirmHtml, $confirmText);
} catch (RuntimeException $e) {
    error_log('[Contact] Confirmation mail failed: ' . $e->getMessage());
}

sendAPIResponse(200, 'Message sent', []);

// ─────────────────────────────────────────────────────────────────────────────

function buildContactMailHtml(
    string $username,
    string $email,
    int $accountLevel,
    string $message,
    string $sentAt
): string
{
    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $message = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

    return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DBuget — Formulaire de contact</title>
<style>
  body { margin:0; padding:0; background:#e7e7e7; font-family:"lato",Arial,Helvetica,sans-serif; color:#333; }
  .wrapper { max-width:560px; margin:32px auto; padding:32px 40px; background:#fff; border-radius:10px; box-shadow:0 0 9px 0 rgba(0,0,0,.1); }
  .title { margin:0 0 4px; font-size:20px; font-weight:700; color:#333; }
  .subtitle { margin:0 0 24px; font-size:13px; color:#888; }
  .info-box { padding:16px 20px; background:#f7f7f7; border-radius:8px; margin-bottom:24px; }
  .info-row { font-size:14px; line-height:1.9; }
  .info-label { color:#888; }
  .info-value { color:#333; font-weight:600; }
  .msg-label { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#aaa; margin-bottom:8px; }
  .msg-body { font-size:15px; line-height:1.7; color:#333; word-break:break-word; }
  .footer { margin-top:28px; padding-top:16px; border-top:1px solid #eee; font-size:12px; color:#bbb; }
</style>
</head>
<body>
<div class="wrapper">
  <h1 class="title">DBuget</h1>
  <p class="subtitle">Formulaire de contact</p>

  <div class="info-box">
    <div class="info-row"><span class="info-label">Username :</span> <span class="info-value">{$username}</span></div>
    <div class="info-row"><span class="info-label">Email :</span> <span class="info-value">{$email}</span></div>
    <div class="info-row"><span class="info-label">Account level :</span> <span class="info-value">{$accountLevel}</span></div>
  </div>

  <div class="msg-label">Message</div>
  <div class="msg-body">{$message}</div>

  <div class="footer">Envoyé le {$sentAt}</div>
</div>
</body>
</html>
HTML;
}

function buildContactMailText(
    string $username,
    string $email,
    int $accountLevel,
    string $message,
    string $sentAt
): string
{
    return "[From DBuget contact form]\r\n"
        . "======== Info =========\r\n"
        . "Username: {$username}\r\n"
        . "Email: {$email}\r\n"
        . "Account level: {$accountLevel}\r\n"
        . "=====================\r\n"
        . "\r\n"
        . $message . "\r\n"
        . "\r\n"
        . "\r\n"
        . "Envoyé le {$sentAt}\r\n";
}

function buildConfirmationMailHtml(
    string $username,
    string $theme,
    string $subject,
    string $message,
    string $sentAt
): string
{
    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $theme = htmlspecialchars($theme, ENT_QUOTES, 'UTF-8');
    $subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
    $message = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
    $heading = trans('settings.contact_form.confirmation.heading');
    $greeting = str_replace('{username}', $username, trans('settings.contact_form.confirmation.greeting'));
    $lead = trans('settings.contact_form.confirmation.lead');
    $themeLabel = trans('settings.contact_form.theme');
    $subjectLabel = trans('settings.contact_form.subject');
    $messageLabel = trans('settings.contact_form.confirmation.your_message');
    $footer = str_replace('{date}', $sentAt, trans('settings.contact_form.confirmation.footer'));

    return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DBuget — {$heading}</title>
<style>
  body { margin:0; padding:0; background:#e7e7e7; font-family:"lato",Arial,Helvetica,sans-serif; color:#333; }
  .wrapper { max-width:560px; margin:32px auto; padding:32px 40px; background:#fff; border-radius:10px; box-shadow:0 0 9px 0 rgba(0,0,0,.1); }
  .title { margin:0 0 4px; font-size:20px; font-weight:700; color:#333; }
  .subtitle { margin:0 0 24px; font-size:13px; color:#888; }
  .lead { font-size:15px; line-height:1.6; color:#333; margin:0 0 24px; }
  .info-box { padding:16px 20px; background:#f7f7f7; border-radius:8px; margin-bottom:24px; }
  .info-row { font-size:14px; line-height:1.9; }
  .info-label { color:#888; }
  .info-value { color:#333; font-weight:600; }
  .msg-label { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#aaa; margin-bottom:8px; }
  .msg-body { font-size:15px; line-height:1.7; color:#333; word-break:break-word; }
  .footer { margin-top:28px; padding-top:16px; border-top:1px solid #eee; font-size:12px; color:#bbb; }
</style>
</head>
<body>
<div class="wrapper">
  <h1 class="title">DBuget</h1>
  <p class="subtitle">{$heading}</p>

  <p class="lead">{$greeting}<br>
  {$lead}</p>

  <div class="info-box">
    <div class="info-row"><span class="info-label">{$themeLabel} :</span> <span class="info-value">{$theme}</span></div>
    <div class="info-row"><span class="info-label">{$subjectLabel} :</span> <span class="info-value">{$subject}</span></div>
  </div>

  <div class="msg-label">{$messageLabel}</div>
  <div class="msg-body">{$message}</div>

  <div class="footer">{$footer}</div>
</div>
</body>
</html>
HTML;
}

function buildConfirmationMailText(
    string $username,
    string $theme,
    string $subject,
    string $message,
    string $sentAt
): string
{
    $greeting = str_replace('{username}', $username, trans('settings.contact_form.confirmation.greeting'));
    $lead = trans('settings.contact_form.confirmation.lead');
    $themeLabel = trans('settings.contact_form.theme');
    $subjectLabel = trans('settings.contact_form.subject');
    $footer = str_replace('{date}', $sentAt, trans('settings.contact_form.confirmation.footer'));

    return "{$greeting}\r\n"
        . "\r\n"
        . "{$lead}\r\n"
        . "\r\n"
        . "=====================\r\n"
        . "{$themeLabel}: {$theme}\r\n"
        . "{$subjectLabel}: {$subject}\r\n"
        . "=====================\r\n"
        . "\r\n"
        . $message . "\r\n"
        . "\r\n"
        . "\r\n"
        . "{$footer}\r\n";
}
