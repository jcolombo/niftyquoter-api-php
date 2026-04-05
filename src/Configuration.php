<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp;

use Adbar\Dot;

class Configuration
{
    private static ?self $instance = null;

    private Dot $data;

    /** @var string[] */
    private array $paths;

    private function __construct(array $defaults, string $path)
    {
        $this->data = new Dot($defaults);
        $this->paths = [$path];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::getInstance()->data->get($key, $default);
    }

    public static function has(string $key): bool
    {
        return self::getInstance()->data->has($key);
    }

    public static function set(string $key, mixed $value): void
    {
        self::getInstance()->data->set($key, $value);
    }

    public static function all(): array
    {
        return self::getInstance()->data->all();
    }

    public static function load(string $path): void
    {
        $instance = self::getInstance();
        $contents = file_get_contents($path);
        $overrideData = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        $merged = array_replace_recursive($instance->data->all(), $overrideData);
        $instance->data = new Dot($merged);
        $instance->paths[] = $path;
    }

    /**
     * Smart load: if path is a directory, look for niftyquoterapi.config.json in it.
     * If path is a file, load it directly.
     *
     * @override OVERRIDE-012: Fixes Paymo dirname() bug — checks is_dir() first.
     */
    public static function overload(string $path): void
    {
        if (is_dir($path)) {
            $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'niftyquoterapi.config.json';
        }
        if (!file_exists($path)) {
            return;
        }
        self::load($path);
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    private static function getInstance(): self
    {
        if (self::$instance === null) {
            $configPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'default.niftyquoterapi.config.json';
            $contents = file_get_contents($configPath);
            $defaults = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
            self::$instance = new self($defaults, $configPath);
        }
        return self::$instance;
    }
}
