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
    /** @var \Ibexa\Contracts\Core\Repository\FieldTypeService */
    private $fieldTypeService;

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
        return 'ezplatform_fieldtype_ezinteger';
    }

    public function getParent()
    {
        return IntegerType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
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

    public function buildView(FormView $view, FormInterface $form, array $options)
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(['min' => null, 'max' => null])
            ->setAllowedTypes('min', ['integer', 'null'])
            ->setAllowedTypes('max', ['integer', 'null']);
    }
}

class_alias(IntegerFieldType::class, 'EzSystems\EzPlatformContentForms\Form\Type\FieldType\IntegerFieldType');
