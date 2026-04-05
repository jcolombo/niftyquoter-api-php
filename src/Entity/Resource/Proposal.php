<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Entity\Resource;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractResource;
use Jcolombo\NiftyquoterApiPhp\Request;
use Jcolombo\NiftyquoterApiPhp\Utility\HttpMethod;

class Proposal extends AbstractResource
{
    public const LABEL = 'Proposal';
    public const API_ENTITY = 'proposal';
    public const API_PATH = 'proposals';
    public const PAGE_SIZE = 20;

    public const REQUIRED_CREATE = ['name', 'user_id'];

    public const READONLY = [
        'id', 'state', 'approved_at', 'rejected_at', 'viewed_at', 'sent_at',
        'created_at', 'template_id', 'total_value', 'overview_url',
        'first_contact_name', 'first_contact_email',
        'first_contact_public_url', 'first_contact_public_url_no_view_tracking',
        'first_contact_public_pdf_url', 'first_contact_public_pdf_url_no_view_tracking',
        'custom_tokens', 'proposal_tags',
    ];

    public const CREATEONLY = [];

    public const WRITEONLY = ['load_template_id', 'load_email_template_id'];
    // Action triggers: load_template_id copies a template into the proposal on create.
    // load_email_template_id sets the email template. Neither is returned in responses.

    public const INCLUDE_TYPES = [];

    public const PROP_TYPES = [
        'id'                 => 'integer',
        'name'               => 'text',
        'code'               => 'text',
        'user_id'            => 'integer',
        'state'              => 'enum:not_sent|sent|viewed|approve|reject',
        'archived'           => 'boolean',
        'approved_at'        => 'datetime',
        'rejected_at'        => 'datetime',
        'viewed_at'          => 'datetime',
        'sent_at'            => 'datetime',
        'created_at'         => 'datetime',
        'currency_id'        => 'integer',
        'theme_id'           => 'integer',
        'template_id'        => 'integer',
        'total_value'        => 'decimal',
        'custom_tokens'      => 'text',
        'proposal_tags'      => 'text',
        'load_template_id'   => 'integer',
        'load_email_template_id' => 'integer',
        'overview_url'       => 'text',
        'first_contact_name' => 'text',
        'first_contact_email' => 'text',
        'first_contact_public_url' => 'text',
        'first_contact_public_url_no_view_tracking' => 'text',
        'first_contact_public_pdf_url' => 'text',
        'first_contact_public_pdf_url_no_view_tracking' => 'text',
    ];

    public const WHERE_OPERATIONS = [
        'state'       => ['='],
        'user_id'     => ['='],
        'currency_id' => ['='],
        'template_id' => ['='],
        'code'        => ['='],
        'archived'    => ['='],
        'from_date'   => ['='],
        'to_date'     => ['='],
    ];

    /**
     * Send an email for this proposal.
     *
     * PUT /proposals/{id}/send_email
     *
     * @return array The response body (notice array)
     * @throws \RuntimeException If the proposal has no ID
     */
    public function sendEmail(
        string $subject,
        string $body,
        ?bool $attachPdf = null,
        ?string $bcc = null,
        ?string $cc = null,
        ?string $replyTo = null,
    ): array {
        if ($this->id === null) {
            throw new \RuntimeException('Cannot send email: proposal has no ID. Fetch or create first.');
        }

        $data = [
            'email_subject' => $subject,
            'email_body' => $body,
        ];
        if ($attachPdf !== null) {
            $data['email_attach_pdf'] = $attachPdf;
        }
        if ($bcc !== null) {
            $data['email_bcc'] = $bcc;
        }
        if ($cc !== null) {
            $data['email_cc'] = $cc;
        }
        if ($replyTo !== null) {
            $data['email_reply_to'] = $replyTo;
        }

        $response = Request::custom(
            $this->connection,
            HttpMethod::PUT,
            static::API_PATH . '/' . $this->id . '/send_email',
            $data
        );

        return $response->body ?? [];
    }

    /**
     * Clone this proposal.
     *
     * PUT /proposals/{id}/clone
     *
     * @return static The new cloned Proposal instance
     * @throws \RuntimeException If the proposal has no ID
     */
    public function clone(
        bool $cloneClient = false,
        bool $cloneComments = false,
        bool $cloneNotes = false,
        bool $expireSource = false,
        bool $archiveSource = false,
        bool $unlinkSource = false,
        bool $noStatsSource = false,
        bool $cloneIntegrations = false,
    ): static {
        if ($this->id === null) {
            throw new \RuntimeException('Cannot clone: proposal has no ID. Fetch or create first.');
        }

        $data = [
            'clone_client' => $cloneClient,
            'clone_comments' => $cloneComments,
            'clone_notes' => $cloneNotes,
            'expire_source' => $expireSource,
            'archive_source' => $archiveSource,
            'unlink_source' => $unlinkSource,
            'no_stats_source' => $noStatsSource,
            'clone_integrations' => $cloneIntegrations,
        ];

        $response = Request::custom(
            $this->connection,
            HttpMethod::PUT,
            static::API_PATH . '/' . $this->id . '/clone',
            $data
        );

        $newProposal = new static($this->connection);
        if ($response->success && $response->body !== null) {
            $proposalData = $response->body[static::API_ENTITY] ?? $response->body;
            $newProposal->hydrate($proposalData);
        }
        return $newProposal;
    }
}
