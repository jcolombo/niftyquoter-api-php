<?php

/**
 * Example 05: Configuration
 *
 * Demonstrates:
 * - Loading custom configuration
 * - Reading and writing config values at runtime
 * - Enabling caching and logging
 * - Configuration cascade (defaults → file → runtime)
 *
 * NOTE: This example does not make API calls.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Jcolombo\NiftyquoterApiPhp\Configuration;

// --- Read defaults (loaded automatically from default.niftyquoterapi.config.json) ---
echo "Default Configuration:\n";
echo "  API URL:     " . Configuration::get('connection.url') . "\n";
echo "  Timeout:     " . Configuration::get('connection.timeout') . "s\n";
echo "  SSL Verify:  " . (Configuration::get('connection.verify') ? 'true' : 'false') . "\n";
echo "  Cache:       " . (Configuration::get('enabled.cache') ? 'enabled' : 'disabled') . "\n";
echo "  Logging:     " . (Configuration::get('enabled.logging') ? 'enabled' : 'disabled') . "\n";
echo "  Dev Mode:    " . (Configuration::get('devMode') ? 'true' : 'false') . "\n";
echo "  Rate Limit:  " . Configuration::get('rateLimit.perMinute') . "/min, "
    . Configuration::get('rateLimit.perHour') . "/hr\n\n";

// --- Override config at runtime ---
echo "Overriding at runtime...\n";
Configuration::set('connection.timeout', 15);
Configuration::set('devMode', true);
echo "  Timeout:  " . Configuration::get('connection.timeout') . "s\n";
echo "  Dev Mode: " . (Configuration::get('devMode') ? 'true' : 'false') . "\n\n";

// --- Load a custom config file (if it exists) ---
// Create a niftyquoterapi.config.json in this directory to test:
//
// {
//   "connection": { "timeout": 10 },
//   "enabled": { "cache": true, "logging": true },
//   "path": { "cache": "/tmp/nqapi-cache", "logs": "/tmp/nqapi-logs" }
// }

$customConfigDir = __DIR__;
Configuration::overload($customConfigDir);
echo "After overload (looked for {$customConfigDir}/niftyquoterapi.config.json):\n";
echo "  Timeout: " . Configuration::get('connection.timeout') . "s\n";
echo "  Cache:   " . (Configuration::get('enabled.cache') ? 'enabled' : 'disabled') . "\n\n";

// --- Check if a key exists ---
echo "Key checks:\n";
echo "  has('connection.url'):     " . (Configuration::has('connection.url') ? 'yes' : 'no') . "\n";
echo "  has('nonexistent.key'):    " . (Configuration::has('nonexistent.key') ? 'yes' : 'no') . "\n";
echo "  get('nonexistent', 'N/A'): " . Configuration::get('nonexistent', 'N/A') . "\n\n";

// --- Reset to defaults ---
Configuration::reset();
echo "After reset:\n";
echo "  Timeout:  " . Configuration::get('connection.timeout') . "s (back to default)\n";
echo "  Dev Mode: " . (Configuration::get('devMode') ? 'true' : 'false') . " (back to default)\n";
