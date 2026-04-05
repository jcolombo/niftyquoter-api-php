<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Tests\ResourceTests;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Proposal;
use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;
use Jcolombo\NiftyquoterApiPhp\Tests\ResourceTest;
use Jcolombo\NiftyquoterApiPhp\Tests\TestOutput;

class ProposalTest extends ResourceTest
{
    private ?int $createdProposalId = null;

    public function getResourceClass(): string
    {
        return Proposal::class;
    }

    public function getResourceName(): string
    {
        return 'Proposal';
    }

    public function getResourceCategory(): string
    {
        return 'top-level';
    }

    protected function createTestResource(NiftyQuoter $connection): AbstractResource
    {
        $proposal = Proposal::new($connection);
        $proposal->name = '[TEST] Proposal';
        $proposal->user_id = $this->config->getUserId();

        return $proposal;
    }

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

            $this->createdProposalId = $resource->id ?? null;

            if ($this->config->isVerbose()) {
                TestOutput::info("Created {$name} with ID: " . ($resource->id ?? 'null'));
            }
        } catch (\Throwable $e) {
            $this->result->fail("{$name}::create", $e->getMessage());
        }
    }

    protected function testFilters(NiftyQuoter $connection): void
    {
        $name = $this->getResourceName();
        $filterParams = [
            'state',
            'user_id',
            'currency_id',
            'template_id',
            'code',
            'archived',
            'from_date',
            'to_date',
        ];

        foreach ($filterParams as $param) {
            try {
                $collection = Proposal::list($connection);
                $collection->where($param, 'test_value');

                $this->assertTrue(
                    true,
                    "{$name}::filters — WHERE '{$param}' accepted"
                );

                if ($this->config->isVerbose()) {
                    TestOutput::info("Filter '{$param}' applied successfully");
                }
            } catch (\Throwable $e) {
                $this->result->fail(
                    "{$name}::filters — WHERE '{$param}'",
                    $e->getMessage()
                );
            }
        }
    }

    public function testSendEmail(NiftyQuoter $connection): void
    {
        $name = $this->getResourceName();

        if ($this->createdProposalId === null) {
            $this->result->skip("{$name}::sendEmail", 'No proposal created to test sendEmail');
            return;
        }

        try {
            $proposal = Proposal::new($connection);
            $proposal->fetch($this->createdProposalId);

            $response = $proposal->sendEmail(
                '[TEST] Email Subject',
                '<p>[TEST] Email Body</p>'
            );

            $this->assertTrue(
                isset($response['notice']),
                "{$name}::sendEmail — response contains 'notice' key"
            );

            if ($this->config->isVerbose()) {
                TestOutput::info("sendEmail response: " . json_encode($response));
            }
        } catch (\Throwable $e) {
            $this->result->fail("{$name}::sendEmail", $e->getMessage());
        }
    }

    public function testClone(NiftyQuoter $connection): void
    {
        $name = $this->getResourceName();

        if ($this->createdProposalId === null) {
            $this->result->skip("{$name}::clone", 'No proposal created to test clone');
            return;
        }

        try {
            $proposal = Proposal::new($connection);
            $proposal->fetch($this->createdProposalId);

            $cloned = $proposal->clone();

            $this->assertNotNull(
                $cloned->id ?? null,
                "{$name}::clone — cloned proposal has an ID"
            );

            $this->assertTrue(
                ($cloned->id ?? null) !== $this->createdProposalId,
                "{$name}::clone — cloned ID differs from original"
            );

            if ($this->config->isVerbose()) {
                TestOutput::info("Cloned proposal ID: " . ($cloned->id ?? 'null'));
            }
        } catch (\Throwable $e) {
            $this->result->fail("{$name}::clone", $e->getMessage());
        }
    }

    public function testPageSize(?NiftyQuoter $connection): void
    {
        $name = $this->getResourceName();

        $this->assertEqual(
            20,
            Proposal::PAGE_SIZE,
            "{$name}::PAGE_SIZE is 20"
        );
    }

    public function run(?NiftyQuoter $connection, \Jcolombo\NiftyquoterApiPhp\Tests\TestConfig $config): \Jcolombo\NiftyquoterApiPhp\Tests\TestResult
    {
        $this->result = new \Jcolombo\NiftyquoterApiPhp\Tests\TestResult();
        $this->config = $config;

        TestOutput::header($this->getResourceName() . ' (' . $this->getResourceCategory() . ')');

        try {
            $this->testPropertyDiscovery($connection);
            $this->testPageSize($connection);

            if ($config->isDryRun()) {
                $this->result->skip($this->getResourceName() . '::create', 'Dry-run mode — skipping API calls');
                $this->result->skip($this->getResourceName() . '::fetch', 'Dry-run mode — skipping API calls');
                $this->result->skip($this->getResourceName() . '::update', 'Dry-run mode — skipping API calls');
                $this->result->skip($this->getResourceName() . '::list', 'Dry-run mode — skipping API calls');
                $this->result->skip($this->getResourceName() . '::filters', 'Dry-run mode — skipping API calls');
                $this->result->skip($this->getResourceName() . '::sendEmail', 'Dry-run mode — skipping API calls');
                $this->result->skip($this->getResourceName() . '::clone', 'Dry-run mode — skipping API calls');
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
                $this->testSendEmail($connection);
                $this->testClone($connection);
                $this->testDelete($connection);
            } else {
                $this->result->skip($this->getResourceName() . '::sendEmail', 'Read-only mode');
                $this->result->skip($this->getResourceName() . '::clone', 'Read-only mode');
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
