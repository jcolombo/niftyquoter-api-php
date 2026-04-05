<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Tests\ResourceTests;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\PricingTable;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Proposal;
use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;
use Jcolombo\NiftyquoterApiPhp\Tests\ResourceTest;
use Jcolombo\NiftyquoterApiPhp\Tests\TestOutput;

class PricingTableTest extends ResourceTest
{
    private ?int $parentProposalId = null;

    public function getResourceClass(): string
    {
        return PricingTable::class;
    }

    public function getResourceName(): string
    {
        return 'PricingTable';
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

        $proposal = Proposal::new($connection);
        $proposal->name = '[TEST] PricingTable Parent Proposal';
        $proposal->user_id = $this->config->getUserId();
        $proposal->create();
        $this->parentProposalId = $proposal->id;

        if ($this->config->isVerbose()) {
            TestOutput::info("Created parent proposal ID: {$this->parentProposalId}");
        }
    }

    protected function createTestResource(NiftyQuoter $connection): AbstractResource
    {
        $table = PricingTable::new($connection);
        $table->forProposal($this->parentProposalId);
        $table->name = '[TEST] Pricing Table';

        return $table;
    }
}
