<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Entity\Collection;

use Jcolombo\NiftyquoterApiPhp\Entity\AbstractCollection;

class PricingTableCollection extends AbstractCollection
{
    protected function validateFetch(array $fields = [], array $where = []): bool
    {
        if (!$this->hasParentContext()) {
            throw new \RuntimeException(
                'PricingTableCollection::fetch() requires a parent proposal context. '
                . 'Use ->forProposal($id) before fetching.'
            );
        }
        return parent::validateFetch($fields, $where);
    }
}
