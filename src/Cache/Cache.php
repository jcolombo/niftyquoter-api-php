<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Cache;

use Jcolombo\NiftyquoterApiPhp\Configuration;
use Jcolombo\NiftyquoterApiPhp\Utility\RequestResponse;

class Cache
{
    private static ?self $instance = null;

    private bool $enabled;

    private ?string $cachePath;

    private int $lifespan = 300;

    /** @var ?callable */
    private $customRead = null;

    /** @var ?callable */
    private $customWrite = null;

    /** @var ?callable */
    private $customClear = null;

    private function __construct()
    {
        $this->enabled = defined('NQAPI_REQUEST_CACHE_PATH') && Configuration::get('enabled.cache') === true;
        $this->cachePath = defined('NQAPI_REQUEST_CACHE_PATH')
            ? rtrim(constant('NQAPI_REQUEST_CACHE_PATH'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'nqapi-cache' . DIRECTORY_SEPARATOR
            : null;
    }

    public static function fetch(string $key): ?RequestResponse
    {
        $self = self::getInstance();
        if (!$self->enabled) {
            return null;
        }

        if ($self->customRead !== null) {
            return ($self->customRead)($key);
        }

        $file = $self->cachePath . $key;
        if (!file_exists($file)) {
            return null;
        }

        // DST-aware validity: use time() - filemtime() to avoid DST transition bugs
        if (time() - filemtime($file) >= $self->lifespan) {
            @unlink($file);
            return null;
        }

        $contents = file_get_contents($file);
        if ($contents === false) {
            return null;
        }

        $response = @unserialize($contents);
        return $response instanceof RequestResponse ? $response : null;
    }

    public static function store(string $key, RequestResponse $response): void
    {
        $self = self::getInstance();
        if (!$self->enabled) {
            return;
        }

        if ($self->customWrite !== null) {
            ($self->customWrite)($key, $response);
            return;
        }

        if (!is_dir($self->cachePath)) {
            mkdir($self->cachePath, 0755, true);
        }

        file_put_contents($self->cachePath . $key, serialize($response));
    }

    public static function clear(?string $key = null): void
    {
        $self = self::getInstance();
        if (!$self->enabled) {
            return;
        }

        if ($self->customClear !== null) {
            ($self->customClear)($key);
            return;
        }

        if ($key !== null) {
            $file = $self->cachePath . $key;
            if (file_exists($file)) {
                @unlink($file);
            }
            return;
        }

        // Clear all cache files
        if ($self->cachePath !== null && is_dir($self->cachePath)) {
            $files = glob($self->cachePath . '*');
            if ($files !== false) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        @unlink($file);
                    }
                }
            }
        }
    }

    public static function registerCacheMethods(callable $read, callable $write, callable $clear): void
    {
        $self = self::getInstance();
        $self->customRead = $read;
        $self->customWrite = $write;
        $self->customClear = $clear;
    }

    public static function isEnabled(): bool
    {
        return self::getInstance()->enabled;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    private static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
