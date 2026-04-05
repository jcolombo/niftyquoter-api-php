<?php

/**
 * Example 01: Basic Connection
 *
 * Demonstrates:
 * - Establishing a connection with credentials
 * - Fetching a single resource by ID
 * - Reading resource properties
 * - Disconnecting
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Client;

// --- Credentials ---
// Set these environment variables, or replace with your values:
//   export NIFTYQUOTER_EMAIL="you@example.com"
//   export NIFTYQUOTER_API_KEY="your-api-key"

$email = getenv('NIFTYQUOTER_EMAIL') ?: 'you@example.com';
$apiKey = getenv('NIFTYQUOTER_API_KEY') ?: 'your-api-key';

// 1. Connect
$nq = NiftyQuoter::connect($email, $apiKey);
echo "Connected as: {$nq->getName()}\n";
echo "API URL: {$nq->getUrl()}\n\n";

// 2. Fetch a single client (replace 1 with a real client ID)
$client = Client::new($nq)->fetch(1);

if ($client->id !== null) {
    echo "Client #{$client->id}\n";
    echo "  Name: {$client->first_name} {$client->last_name}\n";
    echo "  Email: {$client->email}\n";
    echo "  Company: " . ($client->business_name ?? '(none)') . "\n";
} else {
    echo "Client not found (check the ID and your credentials).\n";
}

// 3. Disconnect
NiftyQuoter::disconnect();
echo "\nDisconnected.\n";
