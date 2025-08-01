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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form Type representing ibexa_text field type.
 */
final class TextBlockFieldType extends AbstractType
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
        return 'ezplatform_fieldtype_ibexa_text';
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (null !== $options['rows']) {
            $view->vars['attr'] = array_merge($view->vars['attr'], ['rows' => $options['rows']]);
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new FieldValueTransformer(
            $this->fieldTypeService->getFieldType('ibexa_text')
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('rows', null)
            ->setAllowedTypes('rows', ['integer']);
    }
}
