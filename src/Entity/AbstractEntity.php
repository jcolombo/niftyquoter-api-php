<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Entity;

use Jcolombo\NiftyquoterApiPhp\Configuration;
use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;
use Jcolombo\NiftyquoterApiPhp\Utility\Error;
use Jcolombo\NiftyquoterApiPhp\Utility\ErrorSeverity;

abstract class AbstractEntity
{
    protected ?NiftyQuoter $connection = null;

    protected ?array $parentContext = null;

    public function __construct(null|string|NiftyQuoter|array $connection = null)
    {
        if ($connection instanceof NiftyQuoter) {
            $this->connection = $connection;
        } elseif (is_string($connection)) {
            // Reserved for connection key lookup
            $this->connection = null;
        } elseif (is_array($connection)) {
            // Reserved for config-based construction
            $this->connection = null;
        }

        $this->validateConstants();
    }

    public static function new(null|string|NiftyQuoter $connection = null): static
    {
        return new static($connection);
    }

    public static function list(null|string|NiftyQuoter $connection = null): AbstractCollection
    {
        $resourceKey = EntityMap::extractKey(static::class);
        if ($resourceKey === null) {
            throw new \RuntimeException('Cannot find EntityMap key for class: ' . static::class);
        }

        $entityConfig = EntityMap::entity($resourceKey);
        if ($entityConfig === null || !isset($entityConfig['collectionKey'])) {
            throw new \RuntimeException('Cannot find collection key for: ' . $resourceKey);
        }

        $collectionClass = EntityMap::collection($entityConfig['collectionKey']);
        if ($collectionClass === null) {
            $collectionClass = Configuration::get('classMap.defaultCollection');
        }

        $resolvedConnection = null;
        if ($connection instanceof NiftyQuoter) {
            $resolvedConnection = $connection;
        }

        return new $collectionClass(static::class, $resolvedConnection);
    }

    public function getConnection(): ?NiftyQuoter
    {
        return $this->connection;
    }

    public function setParentContext(string $parentEntity, int $parentId): static
    {
        $this->parentContext = ['entity' => $parentEntity, 'id' => $parentId];
        return $this;
    }

    public function getParentContext(): ?array
    {
        return $this->parentContext;
    }

    public function hasParentContext(): bool
    {
        return $this->parentContext !== null;
    }

    public function getParentPath(): ?string
    {
        if ($this->parentContext === null) {
            return null;
        }
        return $this->parentContext['entity'] . '/' . $this->parentContext['id'];
    }

    protected function resolveConnection(null|string|NiftyQuoter $input): ?NiftyQuoter
    {
        if ($input instanceof NiftyQuoter) {
            return $input;
        }
        if (is_string($input)) {
            // Reserved for future connection key lookup
            return null;
        }
        return null;
    }

    protected function validateConstants(): void
    {
        if (!Configuration::get('devMode')) {
            return;
        }

        if (!defined(static::class . '::REQUIRED_CONSTANTS')) {
            return;
        }

        foreach (static::REQUIRED_CONSTANTS as $name) {
            if (!defined(static::class . '::' . $name)) {
                Error::handle(
                    ErrorSeverity::WARN,
                    'Missing required constant ' . $name . ' on ' . static::class
                );
            }
        }
    }
}
