<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Tests;

use Jcolombo\NiftyquoterApiPhp\Configuration;

class TestConfig
{
    private bool $dryRun = false;

    private bool $verbose = false;

    private bool $readOnly = false;

    private bool $stopOnFailure = false;

    private bool $noCleanup = false;

    private bool $showHelp = false;

    private ?string $resourceFilter = null;

    private ?string $email = null;

    private ?string $apiKey = null;

    private ?int $userId = null;

    private int $listLimit = 5;

    public function parseCli(array $argv): void
    {
        $this->dryRun = Configuration::get('testing.modes.dry_run', false);
        $this->verbose = Configuration::get('testing.modes.verbose', false);
        $this->readOnly = Configuration::get('testing.modes.read_only', false);
        $this->stopOnFailure = Configuration::get('testing.modes.stop_on_failure', false);
        $this->listLimit = Configuration::get('testing.modes.list_limit', 5);

        foreach (array_slice($argv, 1) as $arg) {
            match (true) {
                $arg === '--dry-run' => $this->dryRun = true,
                $arg === '--verbose' => $this->verbose = true,
                $arg === '--read-only' => $this->readOnly = true,
                $arg === '--stop-on-failure' => $this->stopOnFailure = true,
                $arg === '--no-cleanup' => $this->noCleanup = true,
                $arg === '--help' => $this->showHelp = true,
                str_starts_with($arg, '--resource=') => $this->resourceFilter = substr($arg, 11),
                str_starts_with($arg, '--email=') => $this->email = substr($arg, 8),
                str_starts_with($arg, '--api-key=') => $this->apiKey = substr($arg, 10),
                str_starts_with($arg, '--user-id=') => $this->userId = (int) substr($arg, 10),
                default => null,
            };
        }
    }

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    public function isVerbose(): bool
    {
        return $this->verbose;
    }

    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    public function isStopOnFailure(): bool
    {
        return $this->stopOnFailure;
    }

    public function isNoCleanup(): bool
    {
        return $this->noCleanup;
    }

    public function isShowHelp(): bool
    {
        return $this->showHelp;
    }

    public function getResourceFilter(): ?string
    {
        return $this->resourceFilter;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function getUserId(): ?int
    {
        return $this->userId ?? (int) Configuration::get('testing.user_id', null);
    }

    public function getListLimit(): int
    {
        return $this->listLimit;
    }

    public static function printHelp(): void
    {
        echo <<<'HELP'
NiftyQuoter PHP SDK Test Runner

Usage: ./tests/validate [options]

Options:
  --help              Show this help message
  --dry-run           Simulate test execution without API calls
  --read-only         Only run GET operations (no create/update/delete)
  --verbose           Enable verbose output
  --resource=<name>   Run tests for a specific resource only
  --stop-on-failure   Stop after first test failure
  --no-cleanup        Skip cleanup of test entities after run
  --email=<email>     Override test email credential
  --api-key=<key>     Override test API key credential
  --user-id=<id>      Override test user ID for resource creation

Configuration:
  Test settings can be configured in niftyquoterapi.config.json under
  the "testing" key, or in a separate niftyquoterapi.config.test.json file.

HELP;
    }
}
