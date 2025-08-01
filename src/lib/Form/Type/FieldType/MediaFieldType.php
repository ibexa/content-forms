<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\FieldType;

use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form Type representing ibexa_media field type.
 */
final class MediaFieldType extends AbstractType
{
    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ibexa_media';
    }

    public function getParent(): string
    {
        return BinaryBaseFieldType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'hasController',
                CheckboxType::class,
                [
                    'label' => /** @Desc("Display controls") */ 'content.field_type.ibexa_media.display_controls',
                    'required' => false,
                ]
            )
            ->add(
                'autoplay',
                CheckboxType::class,
                [
                    'label' => /** @Desc("Autoplay") */ 'content.field_type.ibexa_media.autoplay',
                    'required' => false,
                ]
            )
            ->add(
                'loop',
                CheckboxType::class,
                [
                    'label' => /** @Desc("Loop") */ 'content.field_type.ibexa_media.loop',
                    'required' => false,
                ]
            )
            ->add(
                'width',
                IntegerType::class,
                [
                    'label' => /** @Desc("Width") */ 'content.field_type.ibexa_media.width',
                    'required' => true,
                    'empty_data' => 0,
                    'attr' => [
                        'step' => 1,
                        'min' => 1,
                    ],
                ]
            )
            ->add(
                'height',
                IntegerType::class,
                [
                    'label' => /** @Desc("Height") */ 'content.field_type.ibexa_media.height',
                    'required' => true,
                    'empty_data' => 0,
                    'attr' => [
                        'step' => 1,
                        'min' => 1,
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['translation_domain' => 'ibexa_content_forms_fieldtype']);
    }
}
