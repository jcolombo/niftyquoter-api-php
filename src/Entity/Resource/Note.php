<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Entity\Resource;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;

class Note extends AbstractResource
{
    public const LABEL = 'Note';
    public const API_ENTITY = 'note';
    public const API_PATH = 'notes';

    public const REQUIRED_CREATE = ['body'];
    // user_id is server-populated from the authenticated user. NOT required for create.
    // Quirk: user_id returns null in the create response but is populated on re-fetch.

    public const READONLY = ['id', 'created_at'];
    public const CREATEONLY = [];
    public const WRITEONLY = [];
    public const INCLUDE_TYPES = [];

    public const PROP_TYPES = [
        'id'         => 'integer',
        'user_id'    => 'integer',
        'body'       => 'text',
        'created_at' => 'datetime',
    ];

    public const WHERE_OPERATIONS = [];

    public function forProposal(int $proposalId): static
    {
        return $this->setParentContext('proposals', $proposalId);
    }
}
