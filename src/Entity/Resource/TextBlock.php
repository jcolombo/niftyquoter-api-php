<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Entity\Resource;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;

class TextBlock extends AbstractResource
{
    public const LABEL = 'Text Block';
    public const API_ENTITY = 'block';
    public const API_PATH = 'blocks';
    // Note: API_PATH is 'blocks', NOT 'text_blocks'. The class name TextBlock
    // does not match the API path — this is intentional to provide a clear
    // class name while respecting the actual API endpoint.

    public const REQUIRED_CREATE = ['internal_name', 'name', 'content'];
    public const READONLY = ['id'];
    public const CREATEONLY = [];
    public const WRITEONLY = [];
    public const INCLUDE_TYPES = [];

    public const PROP_TYPES = [
        'id'            => 'integer',
        'internal_name' => 'text',
        'name'          => 'text',
        'content'       => 'html',
        'tags'          => 'text',     // Comma-separated string, NOT an array
    ];

    public const WHERE_OPERATIONS = [];
}
