<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Tests;

class TestOutput
{
    private const GREEN = "\033[32m";
    private const RED = "\033[31m";
    private const YELLOW = "\033[33m";
    private const BLUE = "\033[34m";
    private const RESET = "\033[0m";
    private const BOLD = "\033[1m";

    public static function header(string $title): void
    {
        $line = str_repeat('─', 60);
        echo "\n" . self::BOLD . self::BLUE . $line . self::RESET . "\n";
        echo self::BOLD . "  {$title}" . self::RESET . "\n";
        echo self::BOLD . self::BLUE . $line . self::RESET . "\n";
    }

    public static function pass(string $message): void
    {
        echo self::GREEN . "  ✓ PASS: {$message}" . self::RESET . "\n";
    }

    public static function fail(string $message): void
    {
        echo self::RED . "  ✗ FAIL: {$message}" . self::RESET . "\n";
    }

    public static function skip(string $message): void
    {
        echo self::YELLOW . "  ○ SKIP: {$message}" . self::RESET . "\n";
    }

    public static function info(string $message): void
    {
        echo self::BLUE . "  ℹ {$message}" . self::RESET . "\n";
    }

    public static function summary(TestResult $result): void
    {
        $summary = $result->getSummary();
        $line = str_repeat('═', 60);

        echo "\n" . self::BOLD . $line . self::RESET . "\n";
        echo self::BOLD . "  TEST SUMMARY" . self::RESET . "\n";
        echo self::BOLD . $line . self::RESET . "\n";
        echo self::GREEN . "  Passed:  {$summary['passed']}" . self::RESET . "\n";
        echo self::RED . "  Failed:  {$summary['failed']}" . self::RESET . "\n";
        echo self::YELLOW . "  Skipped: {$summary['skipped']}" . self::RESET . "\n";
        echo self::BOLD . "  Total:   {$summary['total']}" . self::RESET . "\n";
        echo self::BOLD . $line . self::RESET . "\n";

        if ($result->isSuccess()) {
            echo self::GREEN . self::BOLD . "  ✓ ALL TESTS PASSED" . self::RESET . "\n";
        } else {
            echo self::RED . self::BOLD . "  ✗ SOME TESTS FAILED" . self::RESET . "\n";
            echo "\n  Failed tests:\n";
            foreach ($result->getResults() as $entry) {
                if ($entry['status'] === 'fail') {
                    echo self::RED . "    ✗ {$entry['test']}: {$entry['message']}" . self::RESET . "\n";
                }
            }
        }

        echo "\n";
    }
}
