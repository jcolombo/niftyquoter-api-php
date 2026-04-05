<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Utility;

class RequestResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly ?array $body,
        public readonly array $headers,
        public readonly int $responseCode,
        public readonly string $responseReason,
        public readonly float $responseTime,
        public readonly RequestAbstraction $request,
        public readonly mixed $result = null,
        public readonly ?string $fromCacheKey = null,
    ) {}

    public function validBody(string $key, int $minQty = 0): bool
    {
        if ($this->body === null || !isset($this->body[$key])) {
            return false;
        }
        if ($minQty > 0 && is_array($this->body[$key])) {
            return count($this->body[$key]) >= $minQty;
        }
        return true;
    }
}
