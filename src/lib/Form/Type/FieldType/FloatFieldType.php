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
 * Form Type representing ibexa_float field type.
 */
final class FloatFieldType extends AbstractType
{
    public function __construct(private readonly FieldTypeService $fieldTypeService)
    {
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ibexa_float';
    }

    public function getParent(): string
    {
        return NumberType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(
            new FieldValueTransformer(
                $this->fieldTypeService->getFieldType('ibexa_float')
            )
        );
        // Removes NumberToLocalizedStringTransformer which breaks "number" type HTML input
        $builder->resetViewTransformers();
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(['min' => null, 'max' => null])
            ->setAllowedTypes('min', ['float', 'integer', 'null'])
            ->setAllowedTypes('max', ['float', 'integer', 'null']);
    }
}
