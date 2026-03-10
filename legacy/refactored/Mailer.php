<?php

declare(strict_types=1);

namespace legacy\refactored;

class Mailer
{
    public function send(
        string $to,
        string $subject,
        string $message,
    ): void {
        file_put_contents('emails.log', '[' . date('Y-m-d H:i:s') . "] To: {$to}\nSubject: {$subject}\n{$message}\n\n", FILE_APPEND);
    }
}
