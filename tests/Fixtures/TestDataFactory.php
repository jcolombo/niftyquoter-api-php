<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Tests\Fixtures;

use Jcolombo\NiftyquoterApiPhp\Configuration;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Client;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Comment;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Contact;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\EmailTemplate;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Item;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Note;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\PricingTable;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\Proposal;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\ServiceTemplate;
use Jcolombo\NiftyquoterApiPhp\Entity\Resource\TextBlock;
use Jcolombo\NiftyquoterApiPhp\NiftyQuoter;

/**
 * Factory for creating [TEST]-prefixed entities in a consistent, repeatable way.
 */
class TestDataFactory
{
    private NiftyQuoter $connection;
    private string $prefix;

    public function __construct(NiftyQuoter $connection)
    {
        $this->connection = $connection;
        $this->prefix = Configuration::get('testing.prefix', '[TEST]');
    }

    /**
     * Generate a unique name with the test prefix and a random 4-digit suffix.
     */
    public function uniqueName(string $base): string
    {
        $random = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        return "{$this->prefix} {$base} {$random}";
    }

    public function createClient(array $overrides = []): Client
    {
        $defaults = [
            'is_company' => false,
            'first_name' => $this->uniqueName('First'),
            'last_name' => $this->uniqueName('Last'),
            'email' => strtolower(str_replace(' ', '', $this->uniqueName('client'))) . '@example.com',
        ];
        $data = array_merge($defaults, $overrides);

        $client = new Client($this->connection);
        foreach ($data as $key => $value) {
            $client->set($key, $value);
        }
        $client->create();

        CleanupManager::register(Client::class, $client->get('id'));
        return $client;
    }

    public function createProposal(array $overrides = []): Proposal
    {
        $defaults = [
            'name' => $this->uniqueName('Proposal'),
            'user_id' => $overrides['user_id'] ?? Configuration::get('testing.anchors.user_id'),
        ];
        $data = array_merge($defaults, $overrides);

        $proposal = new Proposal($this->connection);
        foreach ($data as $key => $value) {
            $proposal->set($key, $value);
        }
        $proposal->create();

        CleanupManager::register(Proposal::class, $proposal->get('id'));
        return $proposal;
    }

    public function createComment(int $proposalId, array $overrides = []): Comment
    {
        $defaults = [
            'body' => $this->uniqueName('Comment'),
            'user_id' => $overrides['user_id'] ?? Configuration::get('testing.anchors.user_id'),
        ];
        $data = array_merge($defaults, $overrides);

        $comment = new Comment($this->connection);
        $comment->forProposal($proposalId);
        foreach ($data as $key => $value) {
            $comment->set($key, $value);
        }
        $comment->create();

        CleanupManager::register(Comment::class, $comment->get('id'));
        return $comment;
    }

    public function createNote(int $proposalId, array $overrides = []): Note
    {
        $defaults = [
            'body' => $this->uniqueName('Note'),
        ];
        $data = array_merge($defaults, $overrides);

        $note = new Note($this->connection);
        $note->forProposal($proposalId);
        foreach ($data as $key => $value) {
            $note->set($key, $value);
        }
        $note->create();

        CleanupManager::register(Note::class, $note->get('id'));
        return $note;
    }

    public function createContact(int $proposalId, int $clientId, array $overrides = []): Contact
    {
        $defaults = [
            'client_id' => $clientId,
        ];
        $data = array_merge($defaults, $overrides);

        $contact = new Contact($this->connection);
        $contact->forProposal($proposalId);
        foreach ($data as $key => $value) {
            $contact->set($key, $value);
        }
        $contact->create();

        CleanupManager::register(Contact::class, $contact->get('id'));
        return $contact;
    }

    public function createItem(int $proposalId, array $overrides = []): Item
    {
        $defaults = [
            'name' => $this->uniqueName('Item'),
        ];
        $data = array_merge($defaults, $overrides);

        $item = new Item($this->connection);
        $item->forProposal($proposalId);
        foreach ($data as $key => $value) {
            $item->set($key, $value);
        }
        $item->create();

        CleanupManager::register(Item::class, $item->get('id'));
        return $item;
    }

    public function createPricingTable(int $proposalId, array $overrides = []): PricingTable
    {
        $defaults = [
            'name' => $this->uniqueName('PricingTable'),
        ];
        $data = array_merge($defaults, $overrides);

        $table = new PricingTable($this->connection);
        $table->forProposal($proposalId);
        foreach ($data as $key => $value) {
            $table->set($key, $value);
        }
        $table->create();

        CleanupManager::register(PricingTable::class, $table->get('id'));
        return $table;
    }

    public function createServiceTemplate(array $overrides = []): ServiceTemplate
    {
        $defaults = [
            'name' => $this->uniqueName('ServiceTemplate'),
        ];
        $data = array_merge($defaults, $overrides);

        $template = new ServiceTemplate($this->connection);
        foreach ($data as $key => $value) {
            $template->set($key, $value);
        }
        $template->create();

        CleanupManager::register(ServiceTemplate::class, $template->get('id'));
        return $template;
    }

    public function createEmailTemplate(array $overrides = []): EmailTemplate
    {
        $defaults = [
            'name' => $this->uniqueName('EmailTemplate'),
            'subject' => "{$this->prefix} Subject",
            'body' => "<p>{$this->prefix} Body</p>",
        ];
        $data = array_merge($defaults, $overrides);

        $template = new EmailTemplate($this->connection);
        foreach ($data as $key => $value) {
            $template->set($key, $value);
        }
        $template->create();

        CleanupManager::register(EmailTemplate::class, $template->get('id'));
        return $template;
    }

    public function createTextBlock(array $overrides = []): TextBlock
    {
        $defaults = [
            'internal_name' => $this->uniqueName('Block'),
            'name' => $this->uniqueName('Block'),
            'content' => "<p>{$this->prefix} Content</p>",
        ];
        $data = array_merge($defaults, $overrides);

        $block = new TextBlock($this->connection);
        foreach ($data as $key => $value) {
            $block->set($key, $value);
        }
        $block->create();

        CleanupManager::register(TextBlock::class, $block->get('id'));
        return $block;
    }
}
