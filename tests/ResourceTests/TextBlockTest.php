<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Tests\ResourceTests;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\TextBlock;
use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;
use Jcolombo\NiftyquoterApiPhp\Tests\ResourceTest;
use Jcolombo\NiftyquoterApiPhp\Tests\TestOutput;

class TextBlockTest extends ResourceTest
{
    public function getResourceClass(): string
    {
        return TextBlock::class;
    }

    public function getResourceName(): string
    {
        return 'TextBlock';
    }

    public function getResourceCategory(): string
    {
        return 'top-level';
    }

    protected function createTestResource(NiftyQuoter $connection): AbstractResource
    {
        $block = TextBlock::new($connection);
        $block->internal_name = '[TEST] Block Internal';
        $block->name = '[TEST] Block';
        $block->content = '<p>[TEST] Content</p>';

        return $block;
    }

    public function testApiPath(?NiftyQuoter $connection): void
    {
        $name = $this->getResourceName();

        $this->assertEqual(
            'blocks',
            TextBlock::API_PATH,
            "{$name}::apiPath — API_PATH is 'blocks' (not 'text_blocks')"
        );
    }

    public function testTagsFieldType(?NiftyQuoter $connection): void
    {
        $name = $this->getResourceName();

        $this->assertEqual(
            'text',
            TextBlock::PROP_TYPES['tags'] ?? null,
            "{$name}::tagsFieldType — 'tags' is typed as 'text' (comma-separated string, not array)"
        );
    }

    public function run(?NiftyQuoter $connection, \Jcolombo\NiftyquoterApiPhp\Tests\TestConfig $config): \Jcolombo\NiftyquoterApiPhp\Tests\TestResult
    {
        $this->result = new \Jcolombo\NiftyquoterApiPhp\Tests\TestResult();
        $this->config = $config;

        TestOutput::header($this->getResourceName() . ' (' . $this->getResourceCategory() . ')');

        try {
            $this->testPropertyDiscovery($connection);
            $this->testApiPath($connection);
            $this->testTagsFieldType($connection);

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
}
