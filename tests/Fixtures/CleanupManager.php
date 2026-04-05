<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Tests\Fixtures;

use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Client;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Comment;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Contact;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\EmailTemplate;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Item;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Note;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\PricingTable;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Proposal;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\ServiceTemplate;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\TextBlock;
use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;

/**
 * Dependency-ordered teardown — deletes all test entities in reverse-dependency order.
 */
class CleanupManager
{
    /** @var array<string, int[]> Maps resource FQCN → array of created IDs */
    private static array $createdEntities = [];

    /**
     * Cleanup order: children before parents.
     * Items and PricingTables depend on Proposals, Comments/Notes/Contacts depend on Proposals,
     * Proposals depend on Clients. Top-level templates/blocks are independent.
     */
    private const CLEANUP_ORDER = [
        Item::class,
        PricingTable::class,
        Comment::class,
        Note::class,
        Contact::class,
        Proposal::class,
        Client::class,
        ServiceTemplate::class,
        EmailTemplate::class,
        TextBlock::class,
    ];

    /**
     * Record a created entity for later cleanup.
     */
    public static function register(string $resourceClass, int $id): void
    {
        self::$createdEntities[$resourceClass][] = $id;
    }

    /**
     * Delete all registered entities in dependency order.
     * Catches exceptions to continue cleanup even if individual deletes fail.
     */
    public static function cleanup(NiftyQuoter $connection): void
    {
        foreach (self::CLEANUP_ORDER as $resourceClass) {
            $ids = self::$createdEntities[$resourceClass] ?? [];
            foreach ($ids as $id) {
                try {
                    $resource = new $resourceClass($connection);
                    $resource->fetch($id);
                    $resource->delete();
                } catch (\Throwable $e) {
                    // Log but continue — don't let cleanup failures cascade
                    echo "Cleanup warning: could not delete {$resourceClass} #{$id}: {$e->getMessage()}\n";
                }
            }
        }
        self::reset();
    }

    /**
     * Clear all registered entities.
     */
    public static function reset(): void
    {
        self::$createdEntities = [];
    }
}
