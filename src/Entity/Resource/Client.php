<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Entity\Resource;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;

class Client extends AbstractResource
{
    public const LABEL = 'Client';
    public const API_ENTITY = 'client';
    public const API_PATH = 'clients';

    public const REQUIRED_CREATE = ['is_company'];
    // Conditional: when is_company=true, business_name is required.
    // When is_company=false, first_name and last_name are required.
    // The SDK does not enforce this — the API returns a validation error.

    public const READONLY = ['id', 'updated_at', 'created_at'];
    public const CREATEONLY = ['company_name'];
    public const WRITEONLY = ['company_name'];
    // company_name is both CREATEONLY and WRITEONLY: it triggers company
    // creation/assignment on the server. Sent on create, never returned.

    public const INCLUDE_TYPES = [];

    public const PROP_TYPES = [
        'id'              => 'integer',
        'is_company'      => 'boolean',
        'business_name'   => 'text',
        'company_id'      => 'integer',
        'first_name'      => 'text',
        'last_name'       => 'text',
        'title'           => 'text',
        'email'           => 'text',
        'phone'           => 'text',
        'website'         => 'text',
        'company_name'    => 'text',
        'address'         => 'text',
        'address_city'    => 'text',
        'address_zip'     => 'text',
        'address_state'   => 'text',
        'address_country' => 'text',
        'updated_at'      => 'datetime',
        'created_at'      => 'datetime',
    ];

    public const WHERE_OPERATIONS = [
        'only_companies'    => ['='],
        'search_email'      => ['='],
        'search_company'    => ['='],
        'search_first_name' => ['='],
        'search_last_name'  => ['='],
        'search_phone'      => ['='],
        'search_name'       => ['='],
    ];
}
