<?php

namespace Rikudou\JsonApiBundleFilters\Attribute;

use Attribute;
use Rikudou\JsonApiBundleFilters\Enum\ApiPropertyFilterType;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class FilterProperty
{
    public function __construct(
        public ApiPropertyFilterType $type = ApiPropertyFilterType::Boolean,
    ) {
    }
}
