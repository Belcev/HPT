<?php

declare(strict_types=1);

namespace Legacy\New;

class Mailer
{
    public static function send(
        string $to,
        string $subject,
        string $message
    ): void {
        // Simulate sending email
        file_put_contents('emails.log', '[' . date('Y-m-d H:i:s') . "] To: {$to}\nSubject: {$subject}\n{$message}\n\n", FILE_APPEND);
    }
}
