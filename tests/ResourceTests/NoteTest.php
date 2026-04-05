<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Tests\ResourceTests;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Note;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Proposal;
use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;
use Jcolombo\NiftyquoterApiPhp\Tests\ResourceTest;
use Jcolombo\NiftyquoterApiPhp\Tests\TestOutput;

class NoteTest extends ResourceTest
{
    private ?int $parentProposalId = null;

    public function getResourceClass(): string
    {
        return Note::class;
    }

    public function getResourceName(): string
    {
        return 'Note';
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
        $proposal->name = '[TEST] Note Parent Proposal';
        $proposal->user_id = $this->config->getUserId();
        $proposal->create();
        $this->parentProposalId = $proposal->id;

        if ($this->config->isVerbose()) {
            TestOutput::info("Created parent proposal ID: {$this->parentProposalId}");
        }
    }

    protected function createTestResource(NiftyQuoter $connection): AbstractResource
    {
        $note = Note::new($connection);
        $note->forProposal($this->parentProposalId);
        $note->body = '[TEST] Note body';

        return $note;
    }

    public function testUserIdNullOnCreate(NiftyQuoter $connection): void
    {
        $name = $this->getResourceName();

        if ($this->parentProposalId === null) {
            $this->result->skip("{$name}::userIdNullOnCreate", 'No parent proposal');
            return;
        }

        try {
            $note = Note::new($connection);
            $note->forProposal($this->parentProposalId);
            $note->body = '[TEST] Note user_id quirk';
            $note->create();

            $createUserId = $note->user_id ?? 'NOT_SET';

            // user_id is null in create response (API quirk)
            $this->assertTrue(
                $createUserId === null || $createUserId === 'NOT_SET',
                "{$name}::userIdNullOnCreate — user_id is null in create response"
            );

            // Re-fetch to verify user_id is populated
            if ($note->id !== null) {
                $refetched = Note::new($connection);
                $refetched->forProposal($this->parentProposalId);
                $refetched->fetch($note->id);

                $this->assertNotNull(
                    $refetched->user_id ?? null,
                    "{$name}::userIdNullOnCreate — user_id is populated on re-fetch"
                );
            }

            if ($this->config->isVerbose()) {
                TestOutput::info("Create user_id: " . var_export($createUserId, true));
            }
        } catch (\Throwable $e) {
            $this->result->fail("{$name}::userIdNullOnCreate", $e->getMessage());
        }
    }
}
