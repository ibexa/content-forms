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
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form Type representing ezfloat field type.
 */
class FloatFieldType extends AbstractType
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
        return 'ezplatform_fieldtype_ezfloat';
    }

    public function getParent()
    {
        return NumberType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new FieldValueTransformer($this->fieldTypeService->getFieldType('ezfloat')));
        // Removes NumberToLocalizedStringTransformer which breaks "number" type HTML input
        $builder->resetViewTransformers();
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $attributes = ['step' => 'any'];

        if (null !== $options['min']) {
            $attributes['min'] = $options['min'];
        }

        if (null !== $options['max']) {
            $attributes['max'] = $options['max'];
        }

        $view->vars['attr'] = array_merge($view->vars['attr'], $attributes);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(['min' => null, 'max' => null])
            ->setAllowedTypes('min', ['float', 'integer', 'null'])
            ->setAllowedTypes('max', ['float', 'integer', 'null']);
    }
}

class_alias(FloatFieldType::class, 'EzSystems\EzPlatformContentForms\Form\Type\FieldType\FloatFieldType');
