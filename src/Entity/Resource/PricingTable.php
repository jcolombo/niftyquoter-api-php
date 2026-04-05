<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Entity\Resource;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;

class PricingTable extends AbstractResource
{
    public const LABEL = 'Pricing Table';
    public const API_ENTITY = 'pricing_table';
    public const API_PATH = 'pricing_tables';

    public const REQUIRED_CREATE = ['name'];
    public const READONLY = ['id', 'created_at'];
    public const CREATEONLY = [];
    public const WRITEONLY = [];
    public const INCLUDE_TYPES = [];

    public const PROP_TYPES = [
        'id'               => 'integer',
        'name'             => 'text',
        'kind'             => 'enum:default',        // Incomplete enum — accept any value
        'selected'         => 'boolean',
        'show_totals'      => 'boolean',
        'show_grandtotals' => 'boolean',
        'position'         => 'integer',
        'created_at'       => 'datetime',
    ];

    public const WHERE_OPERATIONS = [];

    public function forProposal(int $proposalId): static
    {
        return $this->setParentContext('proposals', $proposalId);
    }
}
