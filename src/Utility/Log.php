<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Utility;

use Jcolombo\NiftyquoterApiPhp\Configuration;

class Log
{
    private static ?self $instance = null;

    private bool $enabled;

    private ?string $logPath;

    private bool $shouldLog = true;

    private function __construct(bool $enabled, ?string $logPath)
    {
        $this->enabled = $enabled;
        $this->logPath = $logPath;
    }

    public static function onlyIf(bool $condition): self
    {
        $instance = self::getInstance();
        $instance->shouldLog = $condition;
        return $instance;
    }

    public function log(string $message, array $context = []): void
    {
        if ($this->shouldLog && $this->enabled && $this->logPath !== null) {
            $line = '[' . date('Y-m-d H:i:s') . '] ' . $message;
            if (!empty($context)) {
                $line .= ' ' . json_encode($context);
            }
            file_put_contents($this->logPath, $line . PHP_EOL, FILE_APPEND);
        }
        $this->shouldLog = true;
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self(
                (bool) Configuration::get('enabled.logging', false),
                Configuration::get('path.logs')
            );
        }
        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
