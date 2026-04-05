<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Utility;

use Jcolombo\NiftyquoterApiPhp\Configuration;

class Converter
{
    /**
     * Map PROP_TYPES type string to PHP type name.
     *
     * @override OVERRIDE-010: checks intEnum: prefix
     */
    public static function getPrimitiveType(string $propType): string
    {
        return match (true) {
            $propType === 'text', $propType === 'html', $propType === 'date', $propType === 'numeric_string' => 'string',
            $propType === 'integer' => 'int',
            $propType === 'decimal' => 'float',
            $propType === 'boolean' => 'bool',
            $propType === 'datetime' => 'DateTimeImmutable',
            str_starts_with($propType, 'resource:') => 'int',
            str_starts_with($propType, 'collection:') => 'array',
            str_starts_with($propType, 'enum:') => 'string',
            str_starts_with($propType, 'intEnum:') => 'int',
            default => 'mixed',
        };
    }

    public static function convertToPhpValue(mixed $value, string $propType): mixed
    {
        if ($value === null) {
            return null;
        }
        return match (true) {
            $propType === 'text', $propType === 'html' => (string) $value,
            $propType === 'integer' => (int) $value,
            $propType === 'decimal' => (float) $value,
            $propType === 'boolean' => (bool) $value,
            $propType === 'date' => (string) $value,
            $propType === 'datetime' => new \DateTimeImmutable($value),
            $propType === 'numeric_string' => (string) $value,
            str_starts_with($propType, 'resource:') => (int) $value,
            str_starts_with($propType, 'collection:') => array_map('intval', (array) $value),
            str_starts_with($propType, 'enum:') => self::validateEnum((string) $value, $propType),
            str_starts_with($propType, 'intEnum:') => self::validateIntEnum((int) $value, $propType),
            default => $value,
        };
    }

    public static function convertForRequest(mixed $value, string $propType): mixed
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof \DateTimeImmutable) {
            return $value->format('c');
        }
        if ($propType === 'boolean') {
            return (bool) $value;
        }
        return $value;
    }

    public static function convertForFilter(mixed $value, string $propType): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if ($value instanceof \DateTimeImmutable) {
            return $value->format('c');
        }
        return (string) $value;
    }

    private static function validateEnum(string $value, string $propType): string
    {
        if (Configuration::get('devMode')) {
            $options = explode('|', substr($propType, 5));
            if (!in_array($value, $options, true)) {
                Error::handle(ErrorSeverity::WARN, "Value '{$value}' not in enum options: {$propType}");
            }
        }
        return $value;
    }

    private static function validateIntEnum(int $value, string $propType): int
    {
        if (Configuration::get('devMode')) {
            $options = array_map('intval', explode('|', substr($propType, 8)));
            if (!in_array($value, $options, true)) {
                Error::handle(ErrorSeverity::WARN, "Value '{$value}' not in intEnum options: {$propType}");
            }
        }
        return $value;
    }
}
