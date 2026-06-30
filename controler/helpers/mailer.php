<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Mailer
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;

    public function __construct(string $host, int $port, string $username, string $password)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Send a plain-text + HTML email.
     *
     * @throws RuntimeException on SMTP error (preserves the previous contract).
     */
    public function send(string $from, string $fromName, string $to, string $subject, string $htmlBody, string $textBody = ''): void
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $this->host;
            $mail->Port = $this->port;
            $mail->SMTPAuth = true;
            $mail->Username = $this->username;
            $mail->Password = $this->password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->Timeout = 30;
            $mail->XMailer = 'DBudget';

            $mail->setFrom($from, $fromName);
            $mail->addReplyTo($from, $fromName);
            $mail->addAddress($to);

            // Flag automated transactional mail (helps deliverability).
            $mail->addCustomHeader('Auto-Submitted', 'auto-generated');

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $textBody !== '' ? $textBody : strip_tags($htmlBody);

            $mail->send();
        } catch (PHPMailerException $e) {
            throw new RuntimeException('SMTP error: ' . $mail->ErrorInfo, 0, $e);
        }
    }
}
