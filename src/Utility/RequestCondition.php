<?php

declare(strict_types=1);

namespace Jcolombo\NiftyquoterApiPhp\Utility;

class RequestCondition
{
    public function __construct(
        public readonly string $type,
        public readonly string $prop,
        public readonly ?string $dataType,
        public readonly mixed $value,
        public readonly string $operator = '=',
        public readonly bool $validate = true,
    ) {}

    public static function where(
        string $prop,
        mixed $value,
        string $operator = '=',
        ?string $dataType = null,
    ): self {
        return new self('where', $prop, $dataType, $value, $operator);
    }

    public static function has(
        string $prop,
        mixed $value,
        string $operator = '=',
        ?string $dataType = null,
    ): self {
        return new self('has', $prop, $dataType, $value, $operator);
    }
}
