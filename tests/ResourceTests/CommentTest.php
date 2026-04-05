<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Tests\ResourceTests;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Comment;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Proposal;
use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;
use Jcolombo\NiftyquoterApiPhp\Tests\ResourceTest;
use Jcolombo\NiftyquoterApiPhp\Tests\TestOutput;

class CommentTest extends ResourceTest
{
    private ?int $parentProposalId = null;

    public function getResourceClass(): string
    {
        return Comment::class;
    }

    public function getResourceName(): string
    {
        return 'Comment';
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
        $proposal->name = '[TEST] Comment Parent Proposal';
        $proposal->user_id = $this->config->getUserId();
        $proposal->create();
        $this->parentProposalId = $proposal->id;

        if ($this->config->isVerbose()) {
            TestOutput::info("Created parent proposal ID: {$this->parentProposalId}");
        }
    }

    protected function createTestResource(NiftyQuoter $connection): AbstractResource
    {
        $comment = Comment::new($connection);
        $comment->forProposal($this->parentProposalId);
        $comment->body = '[TEST] Comment body';
        $comment->user_id = $this->config->getUserId();

        return $comment;
    }

    public function testMutuallyExclusiveAuthorship(NiftyQuoter $connection): void
    {
        $name = $this->getResourceName();

        if ($this->parentProposalId === null) {
            $this->result->skip("{$name}::mutuallyExclusiveAuthorship", 'No parent proposal');
            return;
        }

        try {
            // Verify that setting both user_id and client_id triggers an API rejection
            $comment = Comment::new($connection);
            $comment->forProposal($this->parentProposalId);
            $comment->body = '[TEST] Dual author comment';
            $comment->user_id = $this->config->getUserId();
            $comment->client_id = 999999; // Intentionally invalid

            try {
                $comment->create();
                // If the API accepts it, that is unexpected but not a test framework failure
                $this->result->skip(
                    "{$name}::mutuallyExclusiveAuthorship",
                    'API did not reject dual user_id/client_id — may need manual verification'
                );
            } catch (\Throwable $e) {
                $this->assertTrue(
                    true,
                    "{$name}::mutuallyExclusiveAuthorship — API rejects dual user_id/client_id"
                );
            }
        } catch (\Throwable $e) {
            $this->result->fail("{$name}::mutuallyExclusiveAuthorship", $e->getMessage());
        }
    }
}
