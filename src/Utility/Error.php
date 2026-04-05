<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Utility;

use Jcolombo\NiftyquoterApiPhp\Configuration;
use Jcolombo\NiftyquoterApiPhp\Utility\RequestResponse;

class Error
{
    public static function handle(ErrorSeverity|string $severity, string $message, array $context = []): void
    {
        if (!Configuration::get('error.enabled', true)) {
            return;
        }

        if (is_string($severity)) {
            $severity = ErrorSeverity::from($severity);
        }

        $handlers = Configuration::get("error.handlers.{$severity->value}", []);

        foreach ($handlers as $handler) {
            match ($handler) {
                'log' => Log::getInstance()->log("[{$severity->value}] {$message}", $context),
                'echo' => self::echoError($severity, $message, $context),
                default => null,
            };
        }

        if (Configuration::get('error.triggerPhpErrors', false)) {
            $level = match ($severity) {
                ErrorSeverity::NOTICE => E_USER_NOTICE,
                ErrorSeverity::WARN => E_USER_WARNING,
                ErrorSeverity::FATAL => E_USER_ERROR,
            };
            trigger_error($message, $level);
        }
    }

    /**
     * Parse API error response and dispatch as fatal error.
     *
     * @param \Jcolombo\NiftyquoterApiPhp\Utility\RequestResponse $response
     */
    public static function handleApiError(RequestResponse $response): void
    {
        $message = self::parseErrorBody(
            $response->body ?? null,
            $response->responseCode ?? 0,
            $response->responseReason ?? 'Unknown'
        );
        self::handle(ErrorSeverity::FATAL, $message);
    }

    private static function parseErrorBody(?array $body, int $statusCode, string $reason): string
    {
        if ($body === null) {
            return "HTTP {$statusCode}: {$reason}";
        }
        if (isset($body['message']) && is_string($body['message'])) {
            return $body['message'];
        }
        if (isset($body['error']) && is_string($body['error'])) {
            return $body['error'];
        }
        if (isset($body['errors']) && is_array($body['errors'])) {
            return implode('; ', array_map('strval', $body['errors']));
        }
        if (isset($body['detail']) && is_string($body['detail'])) {
            return $body['detail'];
        }
        return "HTTP {$statusCode}: {$reason}";
    }

    private static function echoError(ErrorSeverity $severity, string $message, array $context): void
    {
        $label = strtoupper($severity->value);
        $output = "[{$label}] {$message}";
        if (!empty($context)) {
            $output .= ' ' . json_encode($context);
        }
        echo $output . PHP_EOL;
    }
}
