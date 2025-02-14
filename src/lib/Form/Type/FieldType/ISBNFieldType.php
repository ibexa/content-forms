<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\FieldType;

use Ibexa\ContentForms\FieldType\DataTransformer\FieldValueTransformer;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form Type representing ezisbn field type.
 */
class ISBNFieldType extends AbstractType
{
    /** @var \Ibexa\Contracts\Core\Repository\FieldTypeService */
    protected $fieldTypeService;

    public function __construct(FieldTypeService $fieldTypeService)
    {
        $this->fieldTypeService = $fieldTypeService;
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ezisbn';
    }

    public function getParent(): ?string
    {
        return TextType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new FieldValueTransformer($this->fieldTypeService->getFieldType('ezisbn')));
    }
}
