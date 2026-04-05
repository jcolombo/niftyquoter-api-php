<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Entity\Collection;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractCollection;

class ItemCollection extends AbstractCollection
{
    protected function validateFetch(array $fields = [], array $where = []): bool
    {
        if (!$this->hasParentContext()) {
            throw new \RuntimeException(
                'ItemCollection::fetch() requires a parent context. '
                . 'Use ->forProposal($id) or ->forServiceTemplate($id) before fetching.'
            );
        }
        return parent::validateFetch($fields, $where);
    }

    /**
     * Set parent context to a service template.
     */
    public function forServiceTemplate(int $templateId): static
    {
        return $this->setParentContext('service_templates', $templateId);
    }
}
