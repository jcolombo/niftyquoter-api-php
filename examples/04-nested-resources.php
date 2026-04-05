<?php

/**
 * Example 04: Nested Resources
 *
 * Demonstrates:
 * - Creating a proposal (parent resource)
 * - Adding items to a proposal
 * - Adding comments and notes
 * - Listing nested resources
 * - Cleanup
 *
 * IMPORTANT: This creates real data. Use a test account.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Proposal;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Item;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Comment;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Note;

$email = getenv('NIFTYQUOTER_EMAIL') ?: 'you@example.com';
$apiKey = getenv('NIFTYQUOTER_API_KEY') ?: 'your-api-key';
// A valid user_id from your NiftyQuoter account (required for proposal creation)
$userId = (int) (getenv('NIFTYQUOTER_USER_ID') ?: 1);

$nq = NiftyQuoter::connect($email, $apiKey);

// --- Create a proposal ---
echo "Creating proposal...\n";
$proposal = Proposal::new($nq);
$proposal->name = '[SDK Example] Nested Resources Demo';
$proposal->user_id = $userId;
$proposal->create();

if ($proposal->id === null) {
    echo "Failed to create proposal. Check credentials and user_id.\n";
    exit(1);
}
echo "Created proposal #{$proposal->id}: {$proposal->name}\n\n";

// --- Add items to the proposal ---
echo "Adding items...\n";
$item1 = Item::new($nq)->forProposal($proposal->id);
$item1->name = 'Website Design';
$item1->price = '3500.00';
$item1->quantity = '1';
$item1->create();
echo "  Item #{$item1->id}: {$item1->name} (\${$item1->price})\n";

$item2 = Item::new($nq)->forProposal($proposal->id);
$item2->name = 'Monthly Hosting';
$item2->price = '49.99';
$item2->quantity = '12';
$item2->period = 'month';
$item2->create();
echo "  Item #{$item2->id}: {$item2->name} (\${$item2->price}/mo x {$item2->quantity})\n\n";

// --- Add a comment ---
echo "Adding comment...\n";
$comment = Comment::new($nq)->forProposal($proposal->id);
$comment->body = 'This is a demo comment from the SDK examples.';
$comment->user_id = $userId;
$comment->create();
echo "  Comment #{$comment->id}: \"{$comment->body}\"\n\n";

// --- Add a note ---
echo "Adding note...\n";
$note = Note::new($nq)->forProposal($proposal->id);
$note->body = 'Internal note: demo created by SDK example script.';
$note->create();
echo "  Note #{$note->id}: \"{$note->body}\"\n\n";

// --- List all items for the proposal ---
echo "Listing items for proposal #{$proposal->id}...\n";
$items = Item::list($nq)->forProposal($proposal->id)->fetch();
echo "  Total items: " . count($items) . "\n";
foreach ($items as $item) {
    echo "    #{$item->id} {$item->name} — \${$item->price}\n";
}

// --- Cleanup ---
echo "\nCleaning up...\n";
foreach ($items as $item) {
    $item->delete();
}
$comment->delete();
$note->delete();
$proposal->delete();
echo "Done. All test entities deleted.\n";

NiftyQuoter::disconnect();
