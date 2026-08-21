<?php
// /src/Service/MailService.php

declare(strict_types=1);

namespace Src\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * MailService
 * Handles sending system emails using PHPMailer via Composer Autoload.
 */
class MailService
{
    /**
     * Sends an email.
     * * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body HTML content
     * @return bool
     * @throws Exception
     */
    public static function send(string $to, string $subject, string $body): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'] ?? '';
            $mail->Port       = (int) ($_ENV['MAIL_PORT'] ?? 465);
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'] ?? '';
            $mail->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
            $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? PHPMailer::ENCRYPTION_SMTPS;

            // GoDaddy's shared mail hosting presents a wildcard cert
            // (*.prod.phx3.secureserver.net) that never matches mail.<domain>,
            // so hostname verification has to be relaxed -- the certificate
            // chain itself is still verified against a trusted CA.
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => true,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => false,
                ],
            ];

            // Recipients
            $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'] ?? '', $_ENV['MAIL_FROM_NAME'] ?? 'Property Management Brokers');
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            return $mail->send();
        } catch (Exception $e) {
            // Log the detailed error, but throw a clean one for the controller
            error_log("Mailer Error: {$mail->ErrorInfo}");
            throw new \Exception("The mail system is currently unavailable.");
        }
    }
}
