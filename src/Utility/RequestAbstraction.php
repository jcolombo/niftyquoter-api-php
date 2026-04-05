<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Utility;

class RequestAbstraction
{
    public function __construct(
        public readonly HttpMethod $method,
        public readonly string $resourceUrl,
        public readonly ?array $data = null,
        public readonly array $include = [],
        public readonly array $where = [],
        public readonly ?int $page = null,
        public readonly ?int $pageSize = null,
    ) {}

    public function makeCacheKey(): string
    {
        $parts = [
            $this->resourceUrl,
            json_encode($this->include),
            json_encode($this->where),
            $this->page,
            $this->pageSize,
        ];
        return 'nqapi-' . md5(implode('|', array_map('strval', $parts)));
    }
}
