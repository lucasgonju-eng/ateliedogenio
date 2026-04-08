<?php

declare(strict_types=1);

namespace AtelieDoGenio\Domain\Service;

interface TokenDecoderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function decode(string $token): array;
}

