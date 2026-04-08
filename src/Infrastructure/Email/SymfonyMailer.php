<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Email;

use Symfony\Component\Mailer\Mailer as SymfonyMailerClient;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

final class SymfonyMailer implements Mailer
{
    private SymfonyMailerClient $mailer;
    private Address $from;

    public function __construct(
        string $host,
        int $port,
        string $username,
        string $password,
        string $encryption,
        string $fromAddress,
        string $fromName
    ) {
        $dsn = sprintf(
            'smtp://%s:%s@%s:%d?encryption=%s',
            urlencode($username),
            urlencode($password),
            $host,
            $port,
            $encryption
        );

        $transport = Transport::fromDsn($dsn);
        $this->mailer = new SymfonyMailerClient($transport);
        $this->from = new Address($fromAddress, $fromName);
    }

    public function send(string $to, string $subject, string $htmlBody, ?string $textBody = null): void
    {
        $email = (new Email())
            ->from($this->from)
            ->to($to)
            ->subject($subject)
            ->html($htmlBody);

        if ($textBody !== null) {
            $email->text($textBody);
        }

        $this->mailer->send($email);
    }

    public function sendWithAttachment(
        string $to,
        string $subject,
        string $htmlBody,
        ?string $textBody,
        string $filePath,
        ?string $filename = null
    ): void {
        $email = (new Email())
            ->from($this->from)
            ->to($to)
            ->subject($subject)
            ->html($htmlBody);

        if ($textBody !== null) {
            $email->text($textBody);
        }

        $email->attachFromPath($filePath, $filename);

        $this->mailer->send($email);
    }
}
