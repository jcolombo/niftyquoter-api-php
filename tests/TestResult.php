<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Tests;

class TestResult
{
    private int $passed = 0;

    private int $failed = 0;

    private int $skipped = 0;

    /** @var array<int, array{test: string, status: string, message: string}> */
    private array $results = [];

    public function pass(string $test, string $message = ''): void
    {
        $this->passed++;
        $this->results[] = ['test' => $test, 'status' => 'pass', 'message' => $message];
    }

    public function fail(string $test, string $message): void
    {
        $this->failed++;
        $this->results[] = ['test' => $test, 'status' => 'fail', 'message' => $message];
    }

    public function skip(string $test, string $message): void
    {
        $this->skipped++;
        $this->results[] = ['test' => $test, 'status' => 'skip', 'message' => $message];
    }

    public function isSuccess(): bool
    {
        return $this->failed === 0;
    }

    /**
     * @return array{passed: int, failed: int, skipped: int, total: int}
     */
    public function getSummary(): array
    {
        return [
            'passed' => $this->passed,
            'failed' => $this->failed,
            'skipped' => $this->skipped,
            'total' => $this->getTotal(),
        ];
    }

    public function getTotal(): int
    {
        return $this->passed + $this->failed + $this->skipped;
    }

    public function getPassed(): int
    {
        return $this->passed;
    }

    public function getFailed(): int
    {
        return $this->failed;
    }

    public function getSkipped(): int
    {
        return $this->skipped;
    }

    /**
     * @return array<int, array{test: string, status: string, message: string}>
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
