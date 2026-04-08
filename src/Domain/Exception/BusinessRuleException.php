<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Exception;

final class BusinessRuleException extends DomainException
{
    public function __construct(
        private readonly string $errorCode,
        string $message
    ) {
        parent::__construct($message);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }
}

