<?php

/**
 * Example 02: CRUD Operations
 *
 * Demonstrates:
 * - Creating a new resource
 * - Fetching it back by ID
 * - Updating properties (dirty tracking sends only changed fields)
 * - Deleting the resource
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Client;

$email = getenv('NIFTYQUOTER_EMAIL') ?: 'you@example.com';
$apiKey = getenv('NIFTYQUOTER_API_KEY') ?: 'your-api-key';

$nq = NiftyQuoter::connect($email, $apiKey);

// --- CREATE ---
echo "Creating client...\n";
$client = Client::new($nq);
$client->is_company = false;
$client->first_name = 'Example';
$client->last_name = 'User';
$client->email = 'example-' . rand(1000, 9999) . '@test.com';
$client->create();

if ($client->id === null) {
    echo "Create failed. Check credentials and try again.\n";
    exit(1);
}
echo "Created client #{$client->id}: {$client->first_name} {$client->last_name}\n\n";

// --- READ ---
echo "Fetching client #{$client->id}...\n";
$fetched = Client::new($nq)->fetch($client->id);
echo "Fetched: {$fetched->first_name} {$fetched->last_name} ({$fetched->email})\n\n";

// --- UPDATE ---
echo "Updating client...\n";
$fetched->last_name = 'Updated';
$fetched->phone = '555-0199';

// Check dirty tracking
echo "Dirty fields: " . implode(', ', $fetched->getDirty()) . "\n";
$fetched->update();
echo "Updated: {$fetched->first_name} {$fetched->last_name} ({$fetched->phone})\n\n";

// --- DELETE ---
echo "Deleting client #{$fetched->id}...\n";
$deleted = $fetched->delete();
echo $deleted ? "Deleted successfully.\n" : "Delete failed.\n";

NiftyQuoter::disconnect();
