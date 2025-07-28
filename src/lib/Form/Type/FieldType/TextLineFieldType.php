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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form Type representing ibexa_string field type.
 */
final class TextLineFieldType extends AbstractType
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
        return 'ibexa_fieldtype_ibexa_string';
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(
            new FieldValueTransformer($this->fieldTypeService->getFieldType('ibexa_string'))
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $attributes = [];

        if (null !== $options['min']) {
            $attributes['data-min'] = $options['min'];
        }

        if (null !== $options['max']) {
            $attributes['data-max'] = $options['max'];
        }

        $view->vars['attr'] = array_merge($view->vars['attr'], $attributes);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'min' => null,
            'max' => null,
        ])
        ->setAllowedTypes('min', ['integer', 'null'])
        ->setAllowedTypes('max', ['integer', 'null']);
    }
}
