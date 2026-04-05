<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Tests;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractCollection;
use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;
use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;

abstract class ResourceTest
{
    protected ?TestResult $result = null;

    protected ?TestConfig $config = null;

    /**
     * Return the FQCN of the resource being tested.
     */
    abstract public function getResourceClass(): string;

    /**
     * Human-readable name for output (e.g., 'Client').
     */
    abstract public function getResourceName(): string;

    /**
     * 'top-level' or 'nested'.
     */
    abstract public function getResourceCategory(): string;

    /**
     * Create a test entity with [TEST] prefix in name fields.
     */
    abstract protected function createTestResource(NiftyQuoter $connection): AbstractResource;

    /**
     * Execute all test categories in order.
     */
    public function run(?NiftyQuoter $connection, TestConfig $config): TestResult
    {
        $this->result = new TestResult();
        $this->config = $config;

        TestOutput::header($this->getResourceName() . ' (' . $this->getResourceCategory() . ')');

        try {
            $this->testPropertyDiscovery($connection);

            if ($config->isDryRun()) {
                $this->result->skip($this->getResourceName() . '::create', 'Dry-run mode — skipping API calls');
                $this->result->skip($this->getResourceName() . '::fetch', 'Dry-run mode — skipping API calls');
                $this->result->skip($this->getResourceName() . '::update', 'Dry-run mode — skipping API calls');
                $this->result->skip($this->getResourceName() . '::list', 'Dry-run mode — skipping API calls');
                $this->result->skip($this->getResourceName() . '::filters', 'Dry-run mode — skipping API calls');
                $this->result->skip($this->getResourceName() . '::delete', 'Dry-run mode — skipping API calls');
                return $this->result;
            }

            $this->setUp($connection);

            if (!$config->isReadOnly()) {
                $this->testCreate($connection);
            } else {
                $this->result->skip($this->getResourceName() . '::create', 'Read-only mode');
            }

            $this->testFetch($connection);
            $this->testPropertySelection($connection);

            if (!$config->isReadOnly()) {
                $this->testUpdate($connection);
            } else {
                $this->result->skip($this->getResourceName() . '::update', 'Read-only mode');
            }

            $this->testList($connection);
            $this->testFilters($connection);

            if (!$config->isReadOnly()) {
                $this->testDelete($connection);
            } else {
                $this->result->skip($this->getResourceName() . '::delete', 'Read-only mode');
            }
        } catch (\Throwable $e) {
            $this->result->fail(
                $this->getResourceName() . '::exception',
                get_class($e) . ': ' . $e->getMessage()
            );
        } finally {
            $this->tearDown();
        }

        return $this->result;
    }

    /**
     * Pre-test setup. Override in subclasses to create parent resources.
     */
    protected function setUp(NiftyQuoter $connection): void
    {
        // No-op by default
    }

    /**
     * Post-test cleanup. Override as needed.
     */
    protected function tearDown(): void
    {
        // No-op by default
    }

    // ── Test Categories ─────────────────────────────────────────────────

    /**
     * Verify PROP_TYPES constant covers expected fields.
     */
    protected function testPropertyDiscovery(?NiftyQuoter $connection): void
    {
        $class = $this->getResourceClass();
        $name = $this->getResourceName();

        $this->assertTrue(
            defined("{$class}::PROP_TYPES"),
            "{$name} defines PROP_TYPES"
        );

        $propTypes = $class::PROP_TYPES;
        $this->assertTrue(
            is_array($propTypes) && count($propTypes) > 0,
            "{$name}::PROP_TYPES is a non-empty array"
        );
    }

    /**
     * Verify field selection on fetch.
     */
    protected function testPropertySelection(NiftyQuoter $connection): void
    {
        $this->result->skip(
            $this->getResourceName() . '::propertySelection',
            'Property selection tested during fetch'
        );
    }

    /**
     * Create entity with [TEST] prefix, verify ID returned.
     */
    protected function testCreate(NiftyQuoter $connection): void
    {
        $name = $this->getResourceName();

        try {
            $resource = $this->createTestResource($connection);
            $resource->create();

            $this->assertNotNull(
                $resource->id ?? null,
                "{$name}::create returns an entity with an ID"
            );

            if ($this->config->isVerbose()) {
                TestOutput::info("Created {$name} with ID: " . ($resource->id ?? 'null'));
            }
        } catch (\Throwable $e) {
            $this->result->fail("{$name}::create", $e->getMessage());
        }
    }

    /**
     * Fetch by ID, verify fields match.
     */
    protected function testFetch(NiftyQuoter $connection): void
    {
        $this->result->skip(
            $this->getResourceName() . '::fetch',
            'Fetch tested in subclass or after create'
        );
    }

    /**
     * Modify a field, update, verify change persists.
     */
    protected function testUpdate(NiftyQuoter $connection): void
    {
        $this->result->skip(
            $this->getResourceName() . '::update',
            'Update tested in subclass'
        );
    }

    /**
     * List entities, verify collection returned.
     */
    protected function testList(NiftyQuoter $connection): void
    {
        $this->result->skip(
            $this->getResourceName() . '::list',
            'List tested in subclass'
        );
    }

    /**
     * Test WHERE operations. Override in subclasses with filter params.
     */
    protected function testFilters(NiftyQuoter $connection): void
    {
        $this->result->skip(
            $this->getResourceName() . '::filters',
            'No filter tests defined'
        );
    }

    /**
     * Delete entity, verify success.
     */
    protected function testDelete(NiftyQuoter $connection): void
    {
        $this->result->skip(
            $this->getResourceName() . '::delete',
            'Delete tested in subclass'
        );
    }

    // ── Assertion Methods ───────────────────────────────────────────────

    protected function assertEqual(mixed $expected, mixed $actual, string $message): void
    {
        if ($expected === $actual) {
            $this->result->pass($message);
            TestOutput::pass($message);
        } else {
            $detail = "Expected " . var_export($expected, true) . ", got " . var_export($actual, true);
            $this->result->fail($message, $detail);
            TestOutput::fail("{$message} — {$detail}");
        }
    }

    protected function assertNotNull(mixed $value, string $message): void
    {
        if ($value !== null) {
            $this->result->pass($message);
            TestOutput::pass($message);
        } else {
            $this->result->fail($message, 'Value was null');
            TestOutput::fail("{$message} — Value was null");
        }
    }

    protected function assertTrue(bool $condition, string $message): void
    {
        if ($condition) {
            $this->result->pass($message);
            TestOutput::pass($message);
        } else {
            $this->result->fail($message, 'Condition was false');
            TestOutput::fail("{$message} — Condition was false");
        }
    }

    protected function assertInstanceOf(string $class, mixed $object, string $message): void
    {
        if ($object instanceof $class) {
            $this->result->pass($message);
            TestOutput::pass($message);
        } else {
            $actual = is_object($object) ? get_class($object) : gettype($object);
            $this->result->fail($message, "Expected instance of {$class}, got {$actual}");
            TestOutput::fail("{$message} — Expected instance of {$class}, got {$actual}");
        }
    }
}
