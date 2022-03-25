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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form Type representing ezboolean field type.
 */
class CheckboxFieldType extends AbstractType
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

    public function getBlockPrefix()
    {
        return 'ezplatform_fieldtype_ezboolean';
    }

    public function getParent()
    {
        return CheckboxType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addModelTransformer(new FieldValueTransformer($this->fieldTypeService->getFieldType('ezboolean')));
    }
}

class_alias(CheckboxFieldType::class, 'EzSystems\EzPlatformContentForms\Form\Type\FieldType\CheckboxFieldType');
