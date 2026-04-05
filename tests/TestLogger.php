<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Tests;

class TestLogger
{
    private ?string $logPath = null;

    private bool $enabled = false;

    public function __construct(?string $logPath = null)
    {
        if ($logPath !== null) {
            $this->setPath($logPath);
        }
    }

    public function setPath(string $path): void
    {
        $this->logPath = $path;
        $this->enabled = true;

        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    public function log(string $message, array $context = []): void
    {
        if (!$this->enabled || $this->logPath === null) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $entry = "[{$timestamp}] {$message}";

        if (!empty($context)) {
            $entry .= ' ' . json_encode($context, JSON_UNESCAPED_SLASHES);
        }

        $entry .= "\n";

        file_put_contents($this->logPath, $entry, FILE_APPEND | LOCK_EX);
    }

    public function logResult(string $test, string $status, string $message): void
    {
        $this->log("{$status}: {$test}", $message !== '' ? ['message' => $message] : []);
    }
}
