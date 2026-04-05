<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Tests\ResourceTests;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Client;
use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;
use Jcolombo\NiftyquoterApiPhp\Tests\ResourceTest;
use Jcolombo\NiftyquoterApiPhp\Tests\TestOutput;

class ClientTest extends ResourceTest
{
    public function getResourceClass(): string
    {
        return Client::class;
    }

    public function getResourceName(): string
    {
        return 'Client';
    }

    public function getResourceCategory(): string
    {
        return 'top-level';
    }

    protected function createTestResource(NiftyQuoter $connection): AbstractResource
    {
        $client = Client::new($connection);
        $client->is_company = false;
        $client->first_name = '[TEST] First';
        $client->last_name = '[TEST] Last';
        $client->email = '[TEST]client@example.com';

        return $client;
    }

    protected function testFilters(NiftyQuoter $connection): void
    {
        $name = $this->getResourceName();
        $filterParams = [
            'only_companies',
            'search_email',
            'search_company',
            'search_first_name',
            'search_last_name',
            'search_phone',
            'search_name',
        ];

        foreach ($filterParams as $param) {
            try {
                $collection = Client::list($connection);
                $collection->where($param, 'test_value');

                $this->assertTrue(
                    true,
                    "{$name}::filters — WHERE '{$param}' accepted"
                );

                if ($this->config->isVerbose()) {
                    TestOutput::info("Filter '{$param}' applied successfully");
                }
            } catch (\Throwable $e) {
                $this->result->fail(
                    "{$name}::filters — WHERE '{$param}'",
                    $e->getMessage()
                );
            }
        }
    }
}
