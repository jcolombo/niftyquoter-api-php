<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Entity\Resource;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;

class EmailTemplate extends AbstractResource
{
    public const LABEL = 'Email Template';
    public const API_ENTITY = 'email_template';
    public const API_PATH = 'email_templates';

    public const REQUIRED_CREATE = ['name', 'subject', 'body'];
    public const READONLY = ['id'];
    public const CREATEONLY = [];
    public const WRITEONLY = [];
    public const INCLUDE_TYPES = [];

    public const PROP_TYPES = [
        'id'         => 'integer',
        'name'       => 'text',
        'subject'    => 'text',
        'body'       => 'html',
        'attach_pdf' => 'boolean',
        'position'   => 'integer',
    ];

    public const WHERE_OPERATIONS = [];
}
