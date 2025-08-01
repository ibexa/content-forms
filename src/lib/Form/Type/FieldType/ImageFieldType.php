<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\FieldType;

use Ibexa\ContentForms\Form\Type\JsonArrayType;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form Type representing ibexa_image field type.
 */
final class ImageFieldType extends AbstractType
{
    public function __construct(private MimeTypesInterface $mimeTypes)
    {
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ibexa_image';
    }

    public function getParent(): string
    {
        return BinaryBaseFieldType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'alternativeText',
                TextType::class,
                [
                    'label' => /** @Desc("Alternative text") */ 'content.field_type.ibexa_image.alternative_text',
                    'required' => $options['is_alternative_text_required'],
                    'block_prefix' => 'ibexa_fieldtype_ibexa_image_alternative_text',
                ]
            )
            ->add(
                'additionalData',
                JsonArrayType::class
            );
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars += [
            'is_alternative_text_required' => $options['is_alternative_text_required'],
            'mime_types' => $options['mime_types'],
            'image_extensions' => $this->getMimeTypesExtensions($options['mime_types']),
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'ibexa_content_forms_fieldtype',
            'is_alternative_text_required' => false,
            'mime_types' => [],
            'image_extensions' => [],
        ]);

        $resolver->setAllowedTypes('is_alternative_text_required', 'bool');
        $resolver->setAllowedTypes('mime_types', ['array']);
        $resolver->setAllowedTypes('image_extensions', ['array']);
    }

    /**
     * @param array<string> $mimeTypes
     *
     * @return array<string, array<string>>
     */
    private function getMimeTypesExtensions(array $mimeTypes): array
    {
        $extensions = [];
        foreach ($mimeTypes as $mimeType) {
            $extensions[$mimeType] = $this->mimeTypes->getExtensions($mimeType);
        }

        return $extensions;
    }
}
