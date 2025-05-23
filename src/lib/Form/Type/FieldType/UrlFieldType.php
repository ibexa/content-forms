<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\FieldType;

use Ibexa\ContentForms\FieldType\DataTransformer\FieldValueTransformer;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form Type representing ezurl field type.
 */
class UrlFieldType extends AbstractType
{
    protected FieldTypeService $fieldTypeService;

    public function __construct(FieldTypeService $fieldTypeService)
    {
        $this->fieldTypeService = $fieldTypeService;
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ezurl';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'link',
                UrlType::class,
                [
                    'label' => /** @Desc("URL") */ 'content.field_type.ezurl.link',
                    'required' => $options['required'],
                    'default_protocol' => 'https',
                ]
            )
            ->add(
                'text',
                TextType::class,
                [
                    'label' => /** @Desc("Text") */ 'content.field_type.ezurl.text',
                    'required' => false,
                ]
            )
            ->addModelTransformer(new FieldValueTransformer($this->fieldTypeService->getFieldType('ezurl')));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'ibexa_content_forms_fieldtype',
        ]);
    }
}
