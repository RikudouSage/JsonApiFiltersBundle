<?php

namespace Rikudou\JsonApiFiltersBundle\Attribute;

use Attribute;
use Rikudou\JsonApiFiltersBundle\Enum\ApiPropertyFilterType;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class FilterProperty
{
    public function __construct(
        public ApiPropertyFilterType $type = ApiPropertyFilterType::Boolean,
    ) {
    }
}
