<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Jcolombo\NiftyquoterApiPhp\Configuration;
use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;
use Jcolombo\NiftyquoterApiPhp\Tests\TestConfig;
use Jcolombo\NiftyquoterApiPhp\Tests\ResourceTestRunner;

// 1. Parse CLI arguments
$testConfig = new TestConfig();
$testConfig->parseCli($argv);

if ($testConfig->isShowHelp()) {
    TestConfig::printHelp();
    exit(0);
}

// 2. Load test configuration overrides
$configPath = __DIR__ . '/../niftyquoterapi.config.json';
if (file_exists($configPath)) {
    Configuration::overload(dirname($configPath));
}
$testConfigPath = __DIR__ . '/../niftyquoterapi.config.test.json';
if (file_exists($testConfigPath)) {
    Configuration::load($testConfigPath);
}

// 3. Enable devMode for testing
Configuration::set('devMode', true);

// 4. Handle dry-run mode (no credentials needed)
if ($testConfig->isDryRun()) {
    $runner = new ResourceTestRunner($testConfig, null);
    $exitCode = $runner->run();
    exit($exitCode);
}

// 5. Resolve test credentials
$email = $testConfig->getEmail() ?? Configuration::get('testing.email');
$apiKey = $testConfig->getApiKey() ?? Configuration::get('testing.api_key');

if ($email === null || $apiKey === null) {
    echo "ERROR: Test credentials required. Set testing.email and testing.api_key in config.\n";
    echo "       Or pass --email=<email> --api-key=<key> on the command line.\n";
    echo "       Use --dry-run to simulate without credentials.\n";
    exit(1);
}

// 6. Create connection
$connection = NiftyQuoter::connect($email, $apiKey);

// 7. Register shutdown cleanup
if (!$testConfig->isNoCleanup()) {
    register_shutdown_function(function () use ($connection) {
        if (class_exists(\Jcolombo\NiftyquoterApiPhp\Tests\Fixtures\CleanupManager::class)) {
            \Jcolombo\NiftyquoterApiPhp\Tests\Fixtures\CleanupManager::cleanup($connection);
        }
    });
}

// 8. Run tests
$runner = new ResourceTestRunner($testConfig, $connection);
$exitCode = $runner->run();

exit($exitCode);
