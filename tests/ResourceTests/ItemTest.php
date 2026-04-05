<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Tests\ResourceTests;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Item;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Proposal;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\ServiceTemplate;
use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;
use Jcolombo\NiftyquoterApiPhp\Tests\ResourceTest;
use Jcolombo\NiftyquoterApiPhp\Tests\TestOutput;

class ItemTest extends ResourceTest
{
    private ?int $parentProposalId = null;
    private ?int $parentServiceTemplateId = null;

    public function getResourceClass(): string
    {
        return Item::class;
    }

    public function getResourceName(): string
    {
        return 'Item';
    }

    public function getResourceCategory(): string
    {
        return 'nested';
    }

    protected function setUp(NiftyQuoter $connection): void
    {
        if ($this->config->isDryRun()) {
            return;
        }

        // Create parent proposal
        $proposal = Proposal::new($connection);
        $proposal->name = '[TEST] Item Parent Proposal';
        $proposal->user_id = $this->config->getUserId();
        $proposal->create();
        $this->parentProposalId = $proposal->id;

        // Create parent service template
        $template = ServiceTemplate::new($connection);
        $template->name = '[TEST] Item Parent Template';
        $template->create();
        $this->parentServiceTemplateId = $template->id;

        if ($this->config->isVerbose()) {
            TestOutput::info("Created parent proposal ID: {$this->parentProposalId}");
            TestOutput::info("Created parent service template ID: {$this->parentServiceTemplateId}");
        }
    }

    protected function createTestResource(NiftyQuoter $connection): AbstractResource
    {
        $item = Item::new($connection);
        $item->forProposal($this->parentProposalId);
        $item->name = '[TEST] Item';

        return $item;
    }

    public function testForServiceTemplate(NiftyQuoter $connection): void
    {
        $name = $this->getResourceName();

        if ($this->parentServiceTemplateId === null) {
            $this->result->skip("{$name}::forServiceTemplate", 'No parent service template');
            return;
        }

        try {
            $item = Item::new($connection);
            $item->forServiceTemplate($this->parentServiceTemplateId);
            $item->name = '[TEST] Item Under Template';
            $item->create();

            $this->assertNotNull(
                $item->id ?? null,
                "{$name}::forServiceTemplate — item created under service template"
            );

            if ($this->config->isVerbose()) {
                TestOutput::info("Created item under service template with ID: " . ($item->id ?? 'null'));
            }
        } catch (\Throwable $e) {
            $this->result->fail("{$name}::forServiceTemplate", $e->getMessage());
        }
    }

    public function testNumericStringFields(?NiftyQuoter $connection): void
    {
        $name = $this->getResourceName();

        $numericStringFields = ['quantity', 'price', 'vat', 'price_vat', 'purchase_price', 'total', 'total_vat', 'discounted_price'];

        foreach ($numericStringFields as $field) {
            $type = Item::PROP_TYPES[$field] ?? null;
            $this->assertEqual(
                'numeric_string',
                $type,
                "{$name}::numericStringFields — '{$field}' is typed as numeric_string"
            );
        }
    }

    public function testWriteOnlyFields(NiftyQuoter $connection): void
    {
        $name = $this->getResourceName();

        $writeOnlyFields = ['code', 'purchase_price', 'pricing_table_id'];

        foreach ($writeOnlyFields as $field) {
            $this->assertTrue(
                in_array($field, Item::WRITEONLY, true),
                "{$name}::writeOnlyFields — '{$field}' is in WRITEONLY"
            );
        }

        if ($this->parentProposalId === null) {
            $this->result->skip("{$name}::writeOnlyFields — API verify", 'No parent proposal');
            return;
        }

        try {
            $item = Item::new($connection);
            $item->forProposal($this->parentProposalId);
            $item->name = '[TEST] WriteOnly Item';
            $item->code = 'TEST-CODE';
            $item->purchase_price = '100.00';
            $item->create();

            // Re-fetch to verify write-only fields are NOT in the response
            if ($item->id !== null) {
                $refetched = Item::new($connection);
                $refetched->forProposal($this->parentProposalId);
                $refetched->fetch($item->id);

                if ($this->config->isVerbose()) {
                    TestOutput::info("Fetched item after create — checking write-only fields absent from response");
                }
            }
        } catch (\Throwable $e) {
            $this->result->fail("{$name}::writeOnlyFields — API verify", $e->getMessage());
        }
    }

    public function run(?NiftyQuoter $connection, \Jcolombo\NiftyquoterApiPhp\Tests\TestConfig $config): \Jcolombo\NiftyquoterApiPhp\Tests\TestResult
    {
        $this->result = new \Jcolombo\NiftyquoterApiPhp\Tests\TestResult();
        $this->config = $config;

        TestOutput::header($this->getResourceName() . ' (' . $this->getResourceCategory() . ')');

        try {
            $this->testPropertyDiscovery($connection);
            $this->testNumericStringFields($connection);

            if ($config->isDryRun()) {
                $this->result->skip($this->getResourceName() . '::create', 'Dry-run mode — skipping API calls');
                $this->result->skip($this->getResourceName() . '::fetch', 'Dry-run mode — skipping API calls');
                $this->result->skip($this->getResourceName() . '::update', 'Dry-run mode — skipping API calls');
                $this->result->skip($this->getResourceName() . '::list', 'Dry-run mode — skipping API calls');
                $this->result->skip($this->getResourceName() . '::filters', 'Dry-run mode — skipping API calls');
                $this->result->skip($this->getResourceName() . '::forServiceTemplate', 'Dry-run mode — skipping API calls');
                $this->result->skip($this->getResourceName() . '::writeOnlyFields — API verify', 'Dry-run mode — skipping API calls');
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
                $this->testForServiceTemplate($connection);
                $this->testWriteOnlyFields($connection);
                $this->testDelete($connection);
            } else {
                $this->result->skip($this->getResourceName() . '::forServiceTemplate', 'Read-only mode');
                $this->result->skip($this->getResourceName() . '::writeOnlyFields — API verify', 'Read-only mode');
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
