<?php

/**
 * Example 03: Collections and Filtering
 *
 * Demonstrates:
 * - Listing all resources (auto-pagination)
 * - Server-side WHERE filters
 * - Client-side HAS post-filters
 * - Manual pagination with limit()
 * - Collection output methods: count, flatten, raw, json
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Client;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Proposal;

$email = getenv('NIFTYQUOTER_EMAIL') ?: 'you@example.com';
$apiKey = getenv('NIFTYQUOTER_API_KEY') ?: 'your-api-key';

$nq = NiftyQuoter::connect($email, $apiKey);

// --- List all clients (auto-paginates) ---
echo "Fetching all clients...\n";
$clients = Client::list($nq)->fetch();
echo "Total clients: " . count($clients) . "\n\n";

// Iterate
foreach ($clients as $client) {
    $name = $client->is_company
        ? $client->business_name
        : "{$client->first_name} {$client->last_name}";
    echo "  #{$client->id} — {$name}\n";
}

// --- WHERE filter (server-side) ---
echo "\nSearching clients by email...\n";
$filtered = Client::list($nq)
    ->where('search_email', 'jane@example.com')
    ->fetch();
echo "Found: " . count($filtered) . " clients\n";

// --- HAS filter (client-side post-filter) ---
echo "\nFiltering proposals with total_value > 5000...\n";
$highValue = Proposal::list($nq)
    ->has('total_value', 5000, '>')
    ->fetch();
echo "High-value proposals: " . count($highValue) . "\n";

// --- Manual pagination ---
echo "\nFetching page 1 of clients (25 per page)...\n";
$page1 = Client::list($nq)->limit(1, 25)->fetch();
echo "Page 1 count: " . count($page1) . "\n";

// --- Collection output methods ---
echo "\nCollection utilities:\n";
$allNames = $clients->flatten('first_name');
echo "  flatten('first_name'): " . implode(', ', array_slice($allNames, 0, 5)) . "...\n";

$raw = $clients->raw();
echo "  raw() keys (IDs): " . implode(', ', array_slice(array_keys($raw), 0, 5)) . "...\n";

echo "  json_encode: " . strlen(json_encode($clients)) . " bytes\n";

NiftyQuoter::disconnect();
