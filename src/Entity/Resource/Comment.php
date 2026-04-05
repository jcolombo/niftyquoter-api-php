<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Entity\Resource;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;

class Comment extends AbstractResource
{
    public const LABEL = 'Comment';
    public const API_ENTITY = 'comment';
    public const API_PATH = 'comments';

    public const REQUIRED_CREATE = ['body'];
    // Behavioral note: one of user_id or client_id must be provided on create
    // (mutually exclusive). The SDK does not enforce this — the API returns a
    // validation error if neither or both are provided.

    public const READONLY = ['id', 'created_at'];
    public const CREATEONLY = [];
    public const WRITEONLY = [];
    public const INCLUDE_TYPES = [];

    public const PROP_TYPES = [
        'id'         => 'integer',
        'user_id'    => 'integer',
        'client_id'  => 'integer',
        'body'       => 'text',
        'created_at' => 'datetime',
    ];

    public const WHERE_OPERATIONS = [];

    /**
     * Set parent context to a proposal.
     */
    public function forProposal(int $proposalId): static
    {
        return $this->setParentContext('proposals', $proposalId);
    }
}
