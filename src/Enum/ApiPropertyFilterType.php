<?php

namespace Rikudou\JsonApiBundleFilters\Enum;

use DateTimeImmutable;
use DateTimeInterface;

enum ApiPropertyFilterType
{
    case Boolean;
    case CurrentDateGreaterThan;
    case CurrentDateLowerThan;

    public function matches(mixed $value): bool
    {
        $callback = match ($this) {
            self::Boolean => fn (mixed $value) => !!$value,
            self::CurrentDateGreaterThan => function (string|DateTimeInterface $value) {
                if (!$value instanceof DateTimeInterface) {
                    $value = new DateTimeImmutable($value);
                }

                $value = DateTimeImmutable::createFromInterface($value);
                $now = new DateTimeImmutable();

                return $now > $value;
            },
            self::CurrentDateLowerThan => function (string|DateTimeInterface $value) {
                if (!$value instanceof DateTimeInterface) {
                    $value = new DateTimeImmutable($value);
                }

                $value = DateTimeImmutable::createFromInterface($value);
                $now = new DateTimeImmutable();

                return $now < $value;
            }
        };

        // @phpstan-ignore argument.type
        return $callback($value);
    }
}
