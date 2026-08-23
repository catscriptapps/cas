<?php
// /server/api/contact.php
declare(strict_types=1);

use Src\Service\MailService;

// 1. Bootstrap once.
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    json_response(['success' => false, 'message' => 'No data.'], 400);
}

$fullName = trim((string)($input['full_name'] ?? ''));
$email    = trim((string)($input['email'] ?? ''));
$subject  = trim((string)($input['subject'] ?? ''));
$message  = trim((string)($input['message'] ?? ''));

if ($fullName === '' || $email === '' || $subject === '' || $message === '') {
    json_response(['success' => false, 'message' => 'All fields are required.'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['success' => false, 'message' => 'Please provide a valid email address.'], 400);
}

try {
    // There's no internal inbox/admin page for these yet -- the contact
    // form simply emails the site's contact address. Local dev skips the
    // actual send (no real SMTP configured) so the form still succeeds.
    $isLocal = ($_ENV['APP_ENV'] ?? '') === 'local';

    if (!$isLocal) {
        $recipient = $_ENV['CONTACT_EMAIL'] ?? ($_ENV['MAIL_FROM_ADDRESS'] ?? '');
        $body = "
            <div style='font-family: \"Quicksand\", sans-serif; color: #214a4b;'>
                <h2 style='color: #298687;'>New Contact Form Submission</h2>
                <p><strong>From:</strong> " . htmlspecialchars($fullName) . " (" . htmlspecialchars($email) . ")</p>
                <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
                <p style='white-space: pre-wrap;'>" . htmlspecialchars($message) . "</p>
            </div>
        ";

        MailService::send($recipient, "Contact Form: {$subject}", $body);
    }

    json_response(['success' => true]);
} catch (Throwable $e) {
    json_response(['success' => false, 'message' => 'Unable to send your message right now. Please try again later.'], 500);
}
