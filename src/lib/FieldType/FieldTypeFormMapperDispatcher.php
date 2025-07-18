<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType;

use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface;
use Ibexa\Core\FieldType\FieldTypeAliasResolverInterface;
use Symfony\Component\Form\FormInterface;

/**
 * FieldType mappers dispatcher.
 *
 * Adds the form elements matching the given Field Data Definition to a given Form.
 */
final class FieldTypeFormMapperDispatcher implements FieldTypeFormMapperDispatcherInterface
{
    /**
     * FieldTypeFormMapperDispatcher constructor.
     *
     * @param \Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface[] $mappers
     */
    public function __construct(
        private readonly FieldTypeAliasResolverInterface $fieldTypeAliasResolver,
        private array $mappers = []
    ) {
    }

    public function addMapper(FieldValueFormMapperInterface $mapper, string $fieldTypeIdentifier): void
    {
        $this->mappers[$fieldTypeIdentifier] = $mapper;
    }

    public function map(FormInterface $form, FieldData $data): void
    {
        $fieldTypeIdentifier = $this->fieldTypeAliasResolver->resolveIdentifier(
            $data->getFieldTypeIdentifier()
        );

        if (!isset($this->mappers[$fieldTypeIdentifier])) {
            return;
        }

        $this->mappers[$fieldTypeIdentifier]->mapFieldValueForm($form, $data);
    }
}
