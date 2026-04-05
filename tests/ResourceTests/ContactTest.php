<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Tests\ResourceTests;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Client;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Contact;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Proposal;
use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;
use Jcolombo\NiftyquoterApiPhp\Tests\ResourceTest;
use Jcolombo\NiftyquoterApiPhp\Tests\TestOutput;

class ContactTest extends ResourceTest
{
    private ?int $parentProposalId = null;
    private ?int $testClientId = null;

    public function getResourceClass(): string
    {
        return Contact::class;
    }

    public function getResourceName(): string
    {
        return 'Contact';
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
        $proposal->name = '[TEST] Contact Parent Proposal';
        $proposal->user_id = $this->config->getUserId();
        $proposal->create();
        $this->parentProposalId = $proposal->id;

        // Create a client to link as a contact
        $client = Client::new($connection);
        $client->is_company = false;
        $client->first_name = '[TEST] Contact';
        $client->last_name = '[TEST] Client';
        $client->email = '[TEST]contact-client@example.com';
        $client->create();
        $this->testClientId = $client->id;

        if ($this->config->isVerbose()) {
            TestOutput::info("Created parent proposal ID: {$this->parentProposalId}");
            TestOutput::info("Created test client ID: {$this->testClientId}");
        }
    }

    protected function createTestResource(NiftyQuoter $connection): AbstractResource
    {
        $contact = Contact::new($connection);
        $contact->forProposal($this->parentProposalId);
        $contact->client_id = $this->testClientId;

        return $contact;
    }

    public function testWrapperKeyBug(NiftyQuoter $connection): void
    {
        $name = $this->getResourceName();

        // Contact::API_RESPONSE_KEY is set to null initially.
        // Live testing may reveal the GET single response uses "client" wrapper
        // instead of "contact" (documentation bug GAP-M5).
        $this->assertTrue(
            defined(Contact::class . '::API_RESPONSE_KEY'),
            "{$name}::wrapperKeyBug — API_RESPONSE_KEY constant is defined"
        );

        if ($this->config->isVerbose()) {
            TestOutput::info("API_RESPONSE_KEY = " . var_export(Contact::API_RESPONSE_KEY, true));
        }
    }
}
