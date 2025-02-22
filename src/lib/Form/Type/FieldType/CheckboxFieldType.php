<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\FieldType;

use Ibexa\ContentForms\FieldType\DataTransformer\FieldValueTransformer;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form Type representing ezboolean field type.
 */
class CheckboxFieldType extends AbstractType
{
    public function __construct(protected FieldTypeService $fieldTypeService)
    {
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ezboolean';
    }

    #[Override]
    public function getParent(): ?string
    {
        return CheckboxType::class;
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addModelTransformer(new FieldValueTransformer($this->fieldTypeService->getFieldType('ezboolean')));
    }
}
