<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Email;

interface Mailer
{
    /**
     * @param string $to
     * @param string $subject
     * @param string $htmlBody
     * @param string|null $textBody
     */
    public function send(string $to, string $subject, string $htmlBody, ?string $textBody = null): void;
}

