<?php

namespace App\Libraries;

use Config\Email as EmailConfig;
use RuntimeException;
use SendGrid;
use SendGrid\Mail\Mail;
use Throwable;

class EmailService
{
    private EmailConfig $config;

    public function __construct(?EmailConfig $config = null)
    {
        $this->config = $config ?? config('Email');
    }

    public function sendHtml(string $toEmail, string $toName, string $subject, string $html): void
    {
        $apiKey = trim((string) $this->config->SMTPPass);
        $fromEmail = trim((string) $this->config->fromEmail);
        $fromName = trim((string) ($this->config->fromName ?: 'Actas'));

        if ($apiKey === '') {
            throw new RuntimeException('No está configurada la API key de SendGrid en email.SMTPPass.');
        }

        if ($fromEmail === '' || ! filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('No está configurado un remitente válido en email.fromEmail.');
        }

        if (! filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('El destinatario no tiene un correo válido.');
        }

        $mail = new Mail();
        $mail->setFrom($fromEmail, $fromName);
        $mail->setSubject($subject);
        $mail->addTo($toEmail, $toName);
        $mail->addContent('text/html', $html);
        $mail->setClickTracking(false, false);

        try {
            $response = (new SendGrid($apiKey))->send($mail);
        } catch (Throwable $e) {
            throw new RuntimeException('SendGrid no respondió: ' . $e->getMessage(), 0, $e);
        }

        $status = $response->statusCode();
        if ($status < 200 || $status >= 300) {
            $body = trim((string) $response->body());
            throw new RuntimeException('SendGrid rechazó el correo. HTTP ' . $status . ($body !== '' ? ': ' . $body : '.'));
        }
    }
}
