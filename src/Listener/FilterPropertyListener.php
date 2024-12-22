<?php

namespace Rikudou\JsonApiFiltersBundle\Listener;

use ReflectionClass;
use ReflectionProperty;
use Rikudou\JsonApiBundle\Attribute\ApiProperty;
use Rikudou\JsonApiBundle\Events\EntityApiResponseCreatedEvent;
use Rikudou\JsonApiBundle\NameResolution\ApiNameResolutionInterface;
use Rikudou\JsonApiBundle\Structure\Collection\JsonApiCollection;
use Rikudou\JsonApiBundle\Structure\JsonApiObject;
use Rikudou\JsonApiFiltersBundle\Attribute\FilterProperty;
use Rikudou\JsonApiFiltersBundle\Enum\ApiPropertyFilterType;

final readonly class FilterPropertyListener
{
    public function __construct(
        private ApiNameResolutionInterface $nameResolution,
    ) {
    }

    public function preResponse(EntityApiResponseCreatedEvent $event): void
    {
        $class = $event->getApiResourceClass();
        assert(class_exists($class));
        $reflection = new ReflectionClass($class);

        foreach ($reflection->getProperties() as $property) {
            if (!$apiAttribute = $this->getAttribute($property, ApiProperty::class)) {
                continue;
            }
            if (!$filterAttribute = $this->getAttribute($property, FilterProperty::class)) {
                continue;
            }

            $original = $apiAttribute->name ?? $this->nameResolution->getAttributeNameFromProperty($property->getName());
            $this->filterBasedOn($original, $filterAttribute->type, $event->getData(), $event);
        }
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $attribute
     *
     * @return T|null
     */
    private function getAttribute(ReflectionProperty $property, string $attribute): ?object
    {
        $attributes = $property->getAttributes($attribute);
        if (!count($attributes)) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    private function filterBasedOn(
        string $field,
        ApiPropertyFilterType $type,
        JsonApiCollection|JsonApiObject|null $data,
        EntityApiResponseCreatedEvent $event,
    ): void {
        if ($data === null) {
            return;
        }

        /** @var array<JsonApiObject> $iterable */
        $iterable = $data instanceof JsonApiCollection ? $data->getData() : [$data];

        foreach ($iterable as $key => $item) {
            foreach ($item->getAttributes() as $attribute) {
                if ($attribute->getName() !== $field) {
                    continue;
                }
                if (!$type->matches($attribute->getValue())) {
                    unset($iterable[$key]);
                }
            }
        }

        if ($data instanceof JsonApiObject) {
            $event->setData($iterable[array_key_first($iterable)]);
        } else {
            $new = new JsonApiCollection();
            foreach ($iterable as $datum) {
                $new->addObject($datum);
            }
            foreach ($data->getMeta() as $meta) {
                $new->addMeta($meta->getName(), $meta->getValue());
            }
            foreach ($data->getLinks() as $link) {
                $new->addLink($link->getName(), $link->getLink());
            }
            foreach ($data->getIncludes() as $include) {
                $new->addInclude($include);
            }

            $event->setData($new);
        }
    }
}
