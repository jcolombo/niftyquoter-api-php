<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Entity;

use Jcolombo\NiftyquoterApiPhp\Configuration;
use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;
use Jcolombo\NiftyquoterApiPhp\Request;
use Jcolombo\NiftyquoterApiPhp\Utility\RequestCondition;

/**
 * @override OVERRIDE-003 Pagination is 1-indexed
 */
abstract class AbstractCollection extends AbstractEntity implements \Iterator, \ArrayAccess, \Countable, \JsonSerializable
{
    /** @var array<int|string, AbstractResource> */
    protected array $data = [];

    /** @var int[] */
    protected array $iteratorKeys = [];

    protected int $index = 0;

    protected string $resourceClass;

    /** @var RequestCondition[] */
    protected array $whereConditions = [];

    /** @var string[] */
    protected array $includeTypes = [];

    /** @override OVERRIDE-003 1-indexed pagination */
    protected int $paginationPage = 1;

    protected int $paginationPageSize = 100;

    /** @var string[] */
    protected array $fields = [];

    protected array $options = [];

    protected bool $fetchedAll = false;

    public function __construct(string $resourceClass, null|string|NiftyQuoter $connection = null)
    {
        parent::__construct($connection);
        $this->resourceClass = $resourceClass;

        // Check for PAGE_SIZE override on the resource class
        if (defined("{$resourceClass}::PAGE_SIZE")) {
            $this->paginationPageSize = $resourceClass::PAGE_SIZE;
        }
    }

    // ── Fetch ───────────────────────────────────────────────────────────

    /**
     * Fetch a single page of results (default behavior).
     *
     * Returns one page at the current page/pageSize settings. Does NOT
     * auto-paginate. Use fetchAll() when you explicitly need every record.
     */
    public function fetch(): static
    {
        $this->validateFetch($this->fields, $this->whereConditions);

        $resourceInstance = new $this->resourceClass($this->connection);
        $objectKey = $resourceInstance->objectKey();
        $parentPath = $this->getParentPath();

        $serverWheres = array_filter($this->whereConditions, fn($c) => $c->type === 'where');
        $clientFilters = array_filter($this->whereConditions, fn($c) => $c->type === 'has');

        $response = Request::list(
            $this->connection,
            $objectKey,
            $this->fields,
            array_values($serverWheres),
            $this->includeTypes,
            $this->paginationPage,
            $this->paginationPageSize,
            $parentPath
        );

        if ($response->success && $response->body !== null) {
            $pluralKey = $resourceInstance::API_PATH;
            $results = $response->body[$pluralKey]
                ?? $response->body[$resourceInstance::API_ENTITY]
                ?? [];

            foreach ($results as $itemData) {
                $resource = new $this->resourceClass($this->connection);
                $resource->hydrate($itemData);

                if (!empty($clientFilters) && !$this->applyHasFilters($resource, $clientFilters)) {
                    continue;
                }

                $id = $resource->get('id');
                $this->data[$id] = $resource;
            }

            // Mark as fully fetched only if this page wasn't full
            $this->fetchedAll = count($results) < $this->paginationPageSize;
        }

        $this->iteratorKeys = array_keys($this->data);
        return $this;
    }

    /**
     * Fetch ALL pages of results by auto-paginating until exhausted.
     *
     * Use with caution on large collections — this may generate many API
     * calls and consume significant rate limit budget.
     */
    public function fetchAll(): static
    {
        $this->validateFetch($this->fields, $this->whereConditions);

        $resourceInstance = new $this->resourceClass($this->connection);
        $objectKey = $resourceInstance->objectKey();
        $parentPath = $this->getParentPath();

        $serverWheres = array_filter($this->whereConditions, fn($c) => $c->type === 'where');
        $clientFilters = array_filter($this->whereConditions, fn($c) => $c->type === 'has');

        $page = $this->paginationPage;

        do {
            $response = Request::list(
                $this->connection,
                $objectKey,
                $this->fields,
                array_values($serverWheres),
                $this->includeTypes,
                $page,
                $this->paginationPageSize,
                $parentPath
            );

            if (!$response->success || $response->body === null) {
                break;
            }

            $pluralKey = $resourceInstance::API_PATH;
            $results = $response->body[$pluralKey]
                ?? $response->body[$resourceInstance::API_ENTITY]
                ?? [];

            foreach ($results as $itemData) {
                $resource = new $this->resourceClass($this->connection);
                $resource->hydrate($itemData);

                if (!empty($clientFilters) && !$this->applyHasFilters($resource, $clientFilters)) {
                    continue;
                }

                $id = $resource->get('id');
                $this->data[$id] = $resource;
            }

            $page++;
        } while (count($results) >= $this->paginationPageSize);

        $this->fetchedAll = true;
        $this->iteratorKeys = array_keys($this->data);
        return $this;
    }

    // ── Fluent Builder Methods ──────────────────────────────────────────

    public function where(string $prop, mixed $value, string $operator = '='): static
    {
        $resourceClass = $this->resourceClass;
        $whereOps = defined("{$resourceClass}::WHERE_OPERATIONS")
            ? $resourceClass::WHERE_OPERATIONS
            : [];

        $dataType = $whereOps[$prop] ?? null;

        if ($dataType === null && Configuration::get('devMode')) {
            \Jcolombo\NiftyquoterApiPhp\Utility\Error::handle(
                \Jcolombo\NiftyquoterApiPhp\Utility\ErrorSeverity::WARN,
                "WHERE property '{$prop}' not found in " . $resourceClass . '::WHERE_OPERATIONS'
            );
        }

        $this->whereConditions[] = RequestCondition::where($prop, $value, $operator, $dataType);
        return $this;
    }

    public function has(string $prop, mixed $value, string $operator = '='): static
    {
        $this->whereConditions[] = RequestCondition::has($prop, $value, $operator);
        return $this;
    }

    public function include(string ...$types): static
    {
        $this->includeTypes = array_merge($this->includeTypes, $types);
        return $this;
    }

    public function limit(?int $page = null, ?int $pageSize = null): static
    {
        if ($page !== null) {
            $this->paginationPage = $page;
        }
        if ($pageSize !== null) {
            $this->paginationPageSize = $pageSize;
        }
        return $this;
    }

    public function fields(string ...$fieldNames): static
    {
        $this->fields = $fieldNames;
        return $this;
    }

    public function options(array $opts): static
    {
        $this->options = $opts;
        return $this;
    }

    // ── Output Methods ──────────────────────────────────────────────────

    /**
     * Return data as-is (resources keyed by ID).
     */
    public function raw(): array
    {
        return $this->data;
    }

    /**
     * Extract a single property from all resources.
     */
    public function flatten(string $property): array
    {
        return array_map(fn($r) => $r->get($property), $this->data);
    }

    // ── Hook Method ─────────────────────────────────────────────────────

    /**
     * Validate preconditions before fetching. Overridden by specialized collections
     * to enforce parent context requirements.
     */
    protected function validateFetch(array $fields = [], array $where = []): bool
    {
        return true;
    }

    // ── Convenience ─────────────────────────────────────────────────────

    public function forProposal(int $proposalId): static
    {
        return $this->setParentContext('proposals', $proposalId);
    }

    // ── Client-Side Filters ─────────────────────────────────────────────

    /**
     * Apply HAS conditions as client-side post-filters.
     *
     * @param AbstractResource $resource
     * @param RequestCondition[] $filters
     */
    protected function applyHasFilters(AbstractResource $resource, array $filters): bool
    {
        foreach ($filters as $condition) {
            $propValue = $resource->get($condition->prop);
            if (!$this->matchesCondition($propValue, $condition->value, $condition->operator)) {
                return false;
            }
        }
        return true;
    }

    protected function matchesCondition(mixed $actual, mixed $expected, string $operator): bool
    {
        return match ($operator) {
            '=' => $actual == $expected,
            '!=' => $actual != $expected,
            '>' => $actual > $expected,
            '>=' => $actual >= $expected,
            '<' => $actual < $expected,
            '<=' => $actual <= $expected,
            'like' => is_string($actual) && is_string($expected) && str_contains(strtolower($actual), strtolower($expected)),
            default => $actual == $expected,
        };
    }

    // ── Iterator ────────────────────────────────────────────────────────

    public function current(): AbstractResource
    {
        return $this->data[$this->iteratorKeys[$this->index]];
    }

    public function key(): int|string
    {
        return $this->iteratorKeys[$this->index];
    }

    public function next(): void
    {
        $this->index++;
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function valid(): bool
    {
        return $this->index < count($this->iteratorKeys);
    }

    // ── ArrayAccess ─────────────────────────────────────────────────────

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): AbstractResource
    {
        return $this->data[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    // ── Countable ───────────────────────────────────────────────────────

    public function count(): int
    {
        return count($this->data);
    }

    // ── JsonSerializable ────────────────────────────────────────────────

    public function jsonSerialize(): array
    {
        return array_values($this->data);
    }
}
