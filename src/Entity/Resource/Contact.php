<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Entity\Resource;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;

class Contact extends AbstractResource
{
    public const LABEL = 'Contact';
    public const API_ENTITY = 'contact';
    public const API_PATH = 'contacts';

    // API_RESPONSE_KEY: GET single may use "client" wrapper instead of "contact"
    // (documentation bug GAP-M5). Set to null initially. If live testing confirms
    // the bug, set to 'client'.
    public const API_RESPONSE_KEY = null;

    public const REQUIRED_CREATE = ['client_id'];
    // Contact is a junction resource linking a Client to a Proposal.
    // The client must already exist.

    public const READONLY = ['id', 'token', 'sent_at', 'created_at'];
    public const CREATEONLY = [];
    public const WRITEONLY = [];
    public const INCLUDE_TYPES = [];

    public const PROP_TYPES = [
        'id'         => 'integer',
        'client_id'  => 'integer',
        'token'      => 'text',
        'sent_at'    => 'datetime',
        'created_at' => 'datetime',
    ];

    public const WHERE_OPERATIONS = [];

    public function forProposal(int $proposalId): static
    {
        return $this->setParentContext('proposals', $proposalId);
    }
}
