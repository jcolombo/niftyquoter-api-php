<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Tests\ResourceTests;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\EmailTemplate;
use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;
use Jcolombo\NiftyquoterApiPhp\Tests\ResourceTest;

class EmailTemplateTest extends ResourceTest
{
    public function getResourceClass(): string
    {
        return EmailTemplate::class;
    }

    public function getResourceName(): string
    {
        return 'EmailTemplate';
    }

    public function getResourceCategory(): string
    {
        return 'top-level';
    }

    protected function createTestResource(NiftyQuoter $connection): AbstractResource
    {
        $template = EmailTemplate::new($connection);
        $template->name = '[TEST] Email Template';
        $template->subject = '[TEST] Subject';
        $template->body = '<p>[TEST] Body</p>';

        return $template;
    }
}
