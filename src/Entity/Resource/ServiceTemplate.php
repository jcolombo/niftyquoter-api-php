<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Entity\Resource;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;

class ServiceTemplate extends AbstractResource
{
    public const LABEL = 'Service Template';
    public const API_ENTITY = 'service_template';
    public const API_PATH = 'service_templates';

    // API_RESPONSE_KEY: list response may use singular "service_template" (docs bug GAP-M5).
    // Set to null initially. If live testing confirms, set override here.
    public const API_RESPONSE_KEY = null;

    public const REQUIRED_CREATE = ['name'];
    public const READONLY = ['id'];
    public const CREATEONLY = [];
    public const WRITEONLY = [];
    public const INCLUDE_TYPES = [];

    public const PROP_TYPES = [
        'id'   => 'integer',
        'name' => 'text',
    ];

    public const WHERE_OPERATIONS = [];
}
