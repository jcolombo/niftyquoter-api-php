<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Entity;

use Jcolombo\NiftyquoterApiPhp\Configuration;
use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;
use Jcolombo\NiftyquoterApiPhp\Request;
use Jcolombo\NiftyquoterApiPhp\Utility\Converter;
use Jcolombo\NiftyquoterApiPhp\Utility\Error;
use Jcolombo\NiftyquoterApiPhp\Utility\ErrorSeverity;

abstract class AbstractResource extends AbstractEntity
{
    protected const REQUIRED_CONSTANTS = [
        'LABEL', 'API_ENTITY', 'API_PATH', 'REQUIRED_CREATE',
        'READONLY', 'CREATEONLY', 'WRITEONLY', 'INCLUDE_TYPES',
        'PROP_TYPES', 'WHERE_OPERATIONS',
    ];

    // Defaults for static analysis — child classes MUST override these
    protected const LABEL = '';
    protected const API_ENTITY = '';
    protected const API_PATH = '';
    protected const REQUIRED_CREATE = [];
    protected const READONLY = [];
    protected const CREATEONLY = [];
    protected const WRITEONLY = [];
    protected const INCLUDE_TYPES = [];
    protected const PROP_TYPES = [];
    protected const WHERE_OPERATIONS = [];

    protected array $props = [];

    protected array $loaded = [];

    protected array $unlisted = [];

    protected array $included = [];

    protected int|string|null $id = null;

    // ── CRUD Methods ────────────────────────────────────────────────────

    /**
     * GET single resource by ID.
     */
    public function fetch(int|string $id, array $fields = [], array $include = []): static
    {
        $response = Request::fetch($this->connection, $this->objectKey(), $id, $fields, $include);

        if ($response->success && $response->body !== null) {
            $entityKey = static::API_ENTITY;
            $data = $response->body[$entityKey] ?? $response->body;
            $this->hydrate(is_array($data) ? $data : []);
        }

        return $this;
    }

    /**
     * POST new resource.
     */
    public function create(): static
    {
        // Validate required fields (warn, don't block)
        foreach (static::REQUIRED_CREATE as $field) {
            if (!isset($this->props[$field])) {
                Error::handle(
                    ErrorSeverity::WARN,
                    static::LABEL . " create: missing required field '{$field}'"
                );
            }
        }

        $response = Request::create(
            $this->connection,
            $this->objectKey(),
            $this->getWritableData(true),
            $this->getParentPath()
        );

        if ($response->success && $response->body !== null) {
            $entityKey = static::API_ENTITY;
            $data = $response->body[$entityKey] ?? $response->body;
            $this->hydrate(is_array($data) ? $data : []);
        }

        return $this;
    }

    /**
     * PUT changed fields only.
     */
    public function update(): static
    {
        $dirty = $this->getDirty();
        if (empty($dirty)) {
            return $this;
        }

        $writableData = $this->getWritableData(false);
        $dirtyData = array_intersect_key($writableData, array_flip($dirty));

        $response = Request::update(
            $this->connection,
            $this->objectKey(),
            $this->id,
            $dirtyData
        );

        if ($response->success && $response->body !== null) {
            $entityKey = static::API_ENTITY;
            $data = $response->body[$entityKey] ?? $response->body;
            $this->hydrate(is_array($data) ? $data : []);
        }

        return $this;
    }

    /**
     * DELETE resource by ID.
     */
    public function delete(): bool
    {
        $response = Request::delete($this->connection, $this->objectKey(), $this->id);
        return $response->success;
    }

    // ── Property Access ─────────────────────────────────────────────────

    public function set(string $property, mixed $value): static
    {
        $this->props[$property] = $this->wash($property, $value);
        return $this;
    }

    public function get(string $property): mixed
    {
        return $this->props[$property] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    public function __isset(string $name): bool
    {
        return isset($this->props[$name]);
    }

    // ── State Methods ───────────────────────────────────────────────────

    /**
     * Type-coerce value according to PROP_TYPES.
     */
    protected function wash(string $property, mixed $value): mixed
    {
        $propTypes = static::PROP_TYPES;
        if (!isset($propTypes[$property])) {
            return $value;
        }
        return Converter::convertToPhpValue($value, $propTypes[$property]);
    }

    /**
     * Reset all state to empty.
     */
    public function clear(): static
    {
        $this->props = [];
        $this->loaded = [];
        $this->unlisted = [];
        $this->included = [];
        $this->id = null;
        return $this;
    }

    /**
     * Check if any or a specific property has changed from the loaded snapshot.
     */
    public function isDirty(?string $property = null): bool
    {
        if ($property !== null) {
            $currentValue = $this->props[$property] ?? null;
            $loadedValue = $this->loaded[$property] ?? null;
            return $currentValue !== $loadedValue;
        }

        return !empty($this->getDirty());
    }

    /**
     * Return array of property names that differ from the loaded snapshot.
     */
    public function getDirty(): array
    {
        $dirty = [];
        foreach ($this->props as $key => $value) {
            if (!array_key_exists($key, $this->loaded) || $value !== $this->loaded[$key]) {
                $dirty[] = $key;
            }
        }
        return $dirty;
    }

    // ── Serialization ───────────────────────────────────────────────────

    /**
     * Populate properties from API response data.
     */
    public function hydrate(array $data): void
    {
        $propTypes = static::PROP_TYPES;
        foreach ($data as $key => $value) {
            if ($key === 'id' || isset($propTypes[$key])) {
                $this->props[$key] = $this->wash($key, $value);
            } else {
                $this->unlisted[$key] = $value;
            }
        }
        $this->id = $this->props['id'] ?? null;
        $this->loaded = $this->props;
    }

    public function toArray(): array
    {
        return $this->props;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Build request body from props, respecting READONLY/CREATEONLY/WRITEONLY.
     */
    protected function getWritableData(bool $isCreate = false): array
    {
        $data = [];
        foreach ($this->props as $key => $value) {
            // Skip readonly fields always
            if (in_array($key, static::READONLY, true)) {
                continue;
            }
            // Skip CREATEONLY fields on update (not create)
            if (!$isCreate && in_array($key, static::CREATEONLY, true)) {
                continue;
            }
            // Include the field — convert for request
            $propType = static::PROP_TYPES[$key] ?? null;
            $data[$key] = $propType !== null
                ? Converter::convertForRequest($value, $propType)
                : $value;
        }
        return $data;
    }

    /**
     * Build objectKey from constants.
     */
    public function objectKey(): string
    {
        $apiPath = static::API_PATH;
        $apiEntity = static::API_ENTITY;

        // If entity is the singular of path, just return path
        // Otherwise return path:entity
        if ($apiEntity === rtrim($apiPath, 's') || $apiEntity . 's' === $apiPath) {
            return $apiPath;
        }
        return $apiPath . ':' . $apiEntity;
    }
}
