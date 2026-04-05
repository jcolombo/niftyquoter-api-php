<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Utility;

use Jcolombo\NiftyquoterApiPhp\Configuration;

class RateLimiter
{
    /** @var array<string, array{timestamps: float[], retryCount: int, retryAfter: ?float}> */
    private static array $state = [];

    public static function waitIfNeeded(string $connectionKey): void
    {
        if (!Configuration::get('rateLimit.enabled', true)) {
            return;
        }

        self::initState($connectionKey);
        $now = microtime(true);

        // 1. Prune timestamps older than 1 hour
        self::$state[$connectionKey]['timestamps'] = array_values(array_filter(
            self::$state[$connectionKey]['timestamps'],
            fn(float $ts) => ($now - $ts) < 3600
        ));

        $timestamps = self::$state[$connectionKey]['timestamps'];
        $perMinute = Configuration::get('rateLimit.perMinute', 30);
        $perHour = Configuration::get('rateLimit.perHour', 1000);
        $buffer = Configuration::get('rateLimit.safetyBuffer', 1);

        // 2. Check minute window
        $lastMinute = array_filter($timestamps, fn(float $ts) => ($now - $ts) < 60);
        if (count($lastMinute) >= ($perMinute - $buffer)) {
            $oldest = min($lastMinute);
            $sleepUntil = $oldest + 60;
            if ($sleepUntil > $now) {
                usleep((int)(($sleepUntil - $now) * 1_000_000));
            }
        }

        // 3. Check hour window
        if (count($timestamps) >= ($perHour - $buffer)) {
            $oldest = min($timestamps);
            $sleepUntil = $oldest + 3600;
            if ($sleepUntil > $now) {
                usleep((int)(($sleepUntil - $now) * 1_000_000));
            }
        }

        // 4. Enforce minimum delay between requests
        $minDelay = Configuration::get('rateLimit.minDelayMs', 200) / 1000;
        if (!empty($timestamps)) {
            $timeSinceLast = microtime(true) - max($timestamps);
            if ($timeSinceLast < $minDelay) {
                usleep((int)(($minDelay - $timeSinceLast) * 1_000_000));
            }
        }
    }

    public static function recordRequest(string $connectionKey): void
    {
        self::initState($connectionKey);
        self::$state[$connectionKey]['timestamps'][] = microtime(true);
    }

    public static function shouldRetry(string $connectionKey): bool
    {
        self::initState($connectionKey);
        return self::$state[$connectionKey]['retryCount'] < Configuration::get('rateLimit.maxRetries', 3);
    }

    public static function waitForRetry(string $connectionKey): void
    {
        self::initState($connectionKey);
        $retryDelayMs = Configuration::get('rateLimit.retryDelayMs', 1000);
        $retryCount = self::$state[$connectionKey]['retryCount'];
        usleep($retryDelayMs * (int)(2 ** $retryCount) * 1000);
        self::$state[$connectionKey]['retryCount']++;
    }

    public static function resetRetry(string $connectionKey): void
    {
        self::initState($connectionKey);
        self::$state[$connectionKey]['retryCount'] = 0;
    }

    /**
     * Stub: check for x-ratelimit-* headers. Currently a no-op since NiftyQuoter headers are undocumented.
     *
     * @override OVERRIDE-009
     */
    public static function updateFromHeaders(string $connectionKey, array $headers): void
    {
        // No-op: NiftyQuoter does not document rate-limit response headers.
        // When headers become available, parse x-ratelimit-remaining, x-ratelimit-reset, etc.
    }

    public static function reset(?string $connectionKey = null): void
    {
        if ($connectionKey === null) {
            self::$state = [];
        } else {
            unset(self::$state[$connectionKey]);
        }
    }

    private static function initState(string $connectionKey): void
    {
        if (!isset(self::$state[$connectionKey])) {
            self::$state[$connectionKey] = [
                'timestamps' => [],
                'retryCount' => 0,
                'retryAfter' => null,
            ];
        }
    }
}
