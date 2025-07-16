<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Transformer;

use JsonException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements \Symfony\Component\Form\DataTransformerInterface<array<string, mixed>, string>
 */
final readonly class JsonToArrayTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        try {
            $encoded = json_encode((object) $value, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new TransformationFailedException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        return $encoded;
    }

    /**
     * @return array<string, mixed>
     */
    public function reverseTransform(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new TransformationFailedException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        return $decoded;
    }
}
