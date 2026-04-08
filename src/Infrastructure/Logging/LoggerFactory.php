<?php

declare(strict_types=1);

namespace AtelieDoGenio\Infrastructure\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\UidProcessor;
use RuntimeException;

final class LoggerFactory
{
    public function __construct(
        private readonly string $channel,
        private readonly string $logPath
    ) {
    }

    public function createDefaultLogger(): Logger
    {
        $path = $this->prepareLogFilePath('application.log');

        $logger = new Logger($this->channel);
        $logger->pushProcessor(new UidProcessor());
        $logger->pushProcessor(new PsrLogMessageProcessor());
        $logger->pushHandler(new StreamHandler($path, Level::Info));

        return $logger;
    }

    private function prepareLogFilePath(string $fileName): string
    {
        if (!is_dir($this->logPath) && !mkdir($concurrentDirectory = $this->logPath, 0775, true) && !is_dir($concurrentDirectory)) {
            throw new RuntimeException(sprintf('Unable to create log directory "%s".', $this->logPath));
        }

        return rtrim($this->logPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
    }
}

