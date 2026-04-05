<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Tests\ResourceTests;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\ServiceTemplate;
use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;
use Jcolombo\NiftyquoterApiPhp\Tests\ResourceTest;
use Jcolombo\NiftyquoterApiPhp\Tests\TestOutput;

class ServiceTemplateTest extends ResourceTest
{
    public function getResourceClass(): string
    {
        return ServiceTemplate::class;
    }

    public function getResourceName(): string
    {
        return 'ServiceTemplate';
    }

    public function getResourceCategory(): string
    {
        return 'top-level';
    }

    protected function createTestResource(NiftyQuoter $connection): AbstractResource
    {
        $template = ServiceTemplate::new($connection);
        $template->name = '[TEST] Service Template';

        return $template;
    }

    public function testWrapperKeyBug(NiftyQuoter $connection): void
    {
        $name = $this->getResourceName();

        // ServiceTemplate::API_RESPONSE_KEY is set to null initially.
        // Live testing may reveal the list response uses singular wrapper key
        // (documentation bug GAP-M5).
        $this->assertTrue(
            defined(ServiceTemplate::class . '::API_RESPONSE_KEY'),
            "{$name}::wrapperKeyBug — API_RESPONSE_KEY constant is defined"
        );

        if ($this->config->isVerbose()) {
            TestOutput::info("API_RESPONSE_KEY = " . var_export(ServiceTemplate::API_RESPONSE_KEY, true));
        }
    }
}
