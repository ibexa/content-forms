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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form Type representing eztext field type.
 */
class TextBlockFieldType extends AbstractType
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
        return 'ezplatform_fieldtype_eztext';
    }

    #[Override]
    public function getParent(): ?string
    {
        return TextareaType::class;
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (null !== $options['rows']) {
            $view->vars['attr'] = array_merge($view->vars['attr'], ['rows' => $options['rows']]);
        }
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new FieldValueTransformer($this->fieldTypeService->getFieldType('eztext')));
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('rows', null)
            ->setAllowedTypes('rows', ['integer']);
    }
}
