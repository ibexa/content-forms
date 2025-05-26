<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\DataTransformer;

use Ibexa\Core\FieldType\Author\Author;
use Ibexa\Core\FieldType\Author\Value;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * DataTransformer for Author\Value.
 *
 * @phpstan-type TAuthorData array{id: int, name: string, email: string}|array{}
 *
 * @implements \Symfony\Component\Form\DataTransformerInterface<\Ibexa\Core\FieldType\Author\Value, list<TAuthorData>>
 */
class AuthorValueTransformer implements DataTransformerInterface
{
    /**
     * @phpstan-return list<TAuthorData>|array{}
     */
    public function transform(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!$value instanceof Value || $value->authors->count() === 0) {
            return [[]];
        }

        $authors = [];
        foreach ($value->authors as $author) {
            $authors[] = [
                'id' => $author->id,
                'name' => $author->name,
                'email' => $author->email,
            ];
        }

        return $authors;
    }

    public function reverseTransform(mixed $value): ?Value
    {
        if (!is_array($value)) {
            return null;
        }

        $authors = [];
        foreach ($value as $authorProperties) {
            $authors[] = new Author($authorProperties);
        }

        return new Value($authors);
    }
}
