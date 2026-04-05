<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Tests;

use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;

class ResourceTestRunner
{
    private TestConfig $config;

    private ?NiftyQuoter $connection;

    private TestLogger $logger;

    public function __construct(TestConfig $config, ?NiftyQuoter $connection)
    {
        $this->config = $config;
        $this->connection = $connection;
        $this->logger = new TestLogger(__DIR__ . '/test-results.log');
    }

    /**
     * Discover test classes, filter by config, execute in order, output summary.
     * Returns 0 if all pass, 1 if any fail.
     */
    public function run(): int
    {
        TestOutput::header('NiftyQuoter PHP SDK Test Suite');

        if ($this->config->isDryRun()) {
            TestOutput::info('Running in DRY-RUN mode — no API calls will be made');
        }
        if ($this->config->isReadOnly()) {
            TestOutput::info('Running in READ-ONLY mode — only GET operations');
        }
        if ($this->config->isVerbose()) {
            TestOutput::info('Verbose output enabled');
        }

        $this->logger->log('Test run started', [
            'dry_run' => $this->config->isDryRun(),
            'read_only' => $this->config->isReadOnly(),
            'resource_filter' => $this->config->getResourceFilter(),
        ]);

        $testClasses = $this->discoverTests();
        $aggregated = new TestResult();
        $hasFailure = false;

        foreach ($testClasses as $testClass) {
            /** @var ResourceTest $test */
            $test = new $testClass();

            // Apply resource filter
            if ($this->config->getResourceFilter() !== null) {
                if (strtolower($test->getResourceName()) !== strtolower($this->config->getResourceFilter())) {
                    continue;
                }
            }

            $result = $test->run($this->connection, $this->config);

            // Aggregate results
            foreach ($result->getResults() as $entry) {
                match ($entry['status']) {
                    'pass' => $aggregated->pass($entry['test'], $entry['message']),
                    'fail' => $aggregated->fail($entry['test'], $entry['message']),
                    'skip' => $aggregated->skip($entry['test'], $entry['message']),
                };

                $this->logger->logResult($entry['test'], $entry['status'], $entry['message']);
            }

            if (!$result->isSuccess()) {
                $hasFailure = true;
                if ($this->config->isStopOnFailure()) {
                    TestOutput::info('Stopping on first failure (--stop-on-failure)');
                    break;
                }
            }
        }

        TestOutput::summary($aggregated);

        $summary = $aggregated->getSummary();
        $this->logger->log('Test run completed', $summary);

        return $hasFailure ? 1 : 0;
    }

    /**
     * Scan tests/ResourceTests/ for *Test.php files. Return in execution order.
     *
     * @return string[] Fully-qualified class names
     */
    private function discoverTests(): array
    {
        // Execution order: top-level first, then nested (dependency order)
        $ordered = [
            'ClientTest',
            'ProposalTest',
            'ServiceTemplateTest',
            'EmailTemplateTest',
            'TextBlockTest',
            'CommentTest',
            'NoteTest',
            'ContactTest',
            'ItemTest',
            'PricingTableTest',
        ];

        $namespace = 'Jcolombo\\NiftyquoterApiPhp\\Tests\\ResourceTests\\';
        $dir = __DIR__ . '/ResourceTests';
        $classes = [];

        foreach ($ordered as $className) {
            $file = $dir . '/' . $className . '.php';
            if (file_exists($file)) {
                $fqcn = $namespace . $className;
                if (class_exists($fqcn)) {
                    $classes[] = $fqcn;
                }
            }
        }

        return $classes;
    }
}
