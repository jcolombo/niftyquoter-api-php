<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Entity;

use Jcolombo\NiftyquoterApiPhp\Configuration;
use Jcolombo\NiftyquoterApiPhp\Utility\Error;
use Jcolombo\NiftyquoterApiPhp\Utility\ErrorSeverity;

class EntityMap
{
    /**
     * Get resource class FQCN from singular key.
     */
    public static function resource(string $key): ?string
    {
        $entry = Configuration::get("classMap.entity.{$key}");
        if ($entry === null || ($entry['type'] ?? null) !== 'resource') {
            return null;
        }
        return $entry['resource'] ?? null;
    }

    /**
     * Get collection class FQCN from plural key.
     */
    public static function collection(string $key): ?string
    {
        $entry = Configuration::get("classMap.entity.{$key}");
        if ($entry === null || ($entry['type'] ?? null) !== 'collection') {
            return null;
        }
        $collection = $entry['collection'] ?? null;
        if ($collection === true) {
            return Configuration::get('classMap.defaultCollection');
        }
        return $collection;
    }

    /**
     * Get full entity config entry by key (singular or plural).
     */
    public static function entity(string $key): ?array
    {
        $entry = Configuration::get("classMap.entity.{$key}");
        return is_array($entry) ? $entry : null;
    }

    /**
     * Check if key exists in classMap.entity.
     */
    public static function exists(string $key): bool
    {
        return Configuration::has("classMap.entity.{$key}");
    }

    /**
     * Return all registered keys from classMap.entity.
     */
    public static function mapKeys(): array
    {
        $entities = Configuration::get('classMap.entity', []);
        return is_array($entities) ? array_keys($entities) : [];
    }

    /**
     * Reverse lookup: FQCN → singular config key.
     */
    public static function extractKey(string $className): ?string
    {
        $allEntities = Configuration::get('classMap.entity', []);
        foreach ($allEntities as $key => $entry) {
            if (($entry['type'] ?? null) === 'resource' && ($entry['resource'] ?? null) === $className) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Replace a resource or collection class at runtime.
     *
     * @override OVERRIDE-011 Correct class name AbstractResource
     */
    public static function overload(string $key, string $className, string $type = 'resource'): void
    {
        if (Configuration::get('devMode')) {
            if ($type === 'resource' && !is_subclass_of($className, AbstractResource::class)) {
                Error::handle(
                    ErrorSeverity::WARN,
                    "EntityMap overload: {$className} is not a subclass of AbstractResource"
                );
                return;
            }
            if ($type === 'collection' && !is_subclass_of($className, AbstractCollection::class)) {
                Error::handle(
                    ErrorSeverity::WARN,
                    "EntityMap overload: {$className} is not a subclass of AbstractCollection"
                );
                return;
            }
        }

        Configuration::set("classMap.entity.{$key}.{$type}", $className);
    }
}
