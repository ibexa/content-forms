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
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form Type representing ezinteger field type.
 */
class IntegerFieldType extends AbstractType
{
    public function __construct(private FieldTypeService $fieldTypeService)
    {
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ezinteger';
    }

    #[Override]
    public function getParent(): ?string
    {
        return IntegerType::class;
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $attributes = ['step' => 1];

        if (null !== $options['min']) {
            $attributes['min'] = $options['min'];
        }

        if (null !== $options['max']) {
            $attributes['max'] = $options['max'];
        }

        $builder->addModelTransformer(new FieldValueTransformer($this->fieldTypeService->getFieldType('ezinteger')));
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $attributes = ['step' => 1];

        if (null !== $options['min']) {
            $attributes['min'] = $options['min'];
        }

        if (null !== $options['max']) {
            $attributes['max'] = $options['max'];
        }

        $view->vars['attr'] = array_merge($view->vars['attr'], $attributes);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(['min' => null, 'max' => null])
            ->setAllowedTypes('min', ['integer', 'null'])
            ->setAllowedTypes('max', ['integer', 'null']);
    }
}
