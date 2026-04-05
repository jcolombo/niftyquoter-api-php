<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Entity\Resource;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;

class Item extends AbstractResource
{
    public const LABEL = 'Item';
    public const API_ENTITY = 'item';
    public const API_PATH = 'items';

    public const REQUIRED_CREATE = ['name'];

    public const READONLY = ['id', 'total', 'total_vat', 'discounted_price'];
    // total, total_vat, discounted_price are server-calculated.

    public const CREATEONLY = [];

    public const WRITEONLY = ['code', 'purchase_price', 'pricing_table_id'];
    // code: write-only identifier, never returned in responses.
    // purchase_price: sent to server but never returned.
    // pricing_table_id: action trigger — assigns item to a pricing table.

    public const INCLUDE_TYPES = [];

    public const PROP_TYPES = [
        'id'               => 'integer',
        'name'             => 'text',
        'code'             => 'text',
        'description'      => 'text',
        'kind'             => 'enum:item',           // Incomplete enum — accept any value
        'pricing_table_id' => 'integer',
        'discount'         => 'decimal',
        'discount_type'    => 'enum:relative',       // Incomplete enum — accept any value
        'optional'         => 'boolean',
        'optional_checked' => 'boolean',
        'quantity'         => 'numeric_string',      // @override OVERRIDE-007
        'price'            => 'numeric_string',
        'vat'              => 'numeric_string',
        'price_vat'        => 'numeric_string',
        'purchase_price'   => 'numeric_string',
        'period'           => 'text',
        'position'         => 'integer',
        'total'            => 'numeric_string',
        'total_vat'        => 'numeric_string',
        'discounted_price' => 'numeric_string',
    ];

    public const WHERE_OPERATIONS = [];

    /**
     * Set parent context to a proposal.
     */
    public function forProposal(int $proposalId): static
    {
        return $this->setParentContext('proposals', $proposalId);
    }

    /**
     * Set parent context to a service template.
     * Items can belong to either proposals or service templates (polymorphic parent).
     */
    public function forServiceTemplate(int $templateId): static
    {
        return $this->setParentContext('service_templates', $templateId);
    }
}
