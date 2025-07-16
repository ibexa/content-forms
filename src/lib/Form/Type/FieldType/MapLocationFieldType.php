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
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form Type representing ibexa_gmap_location field type.
 */
final class MapLocationFieldType extends AbstractType
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
        return 'ezplatform_fieldtype_ibexa_gmap_location';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'latitude',
                NumberType::class,
                [
                    'label' => /** @Desc("Latitude") */ 'content.field_type.ibexa_gmap_location.latitude',
                    'required' => $options['required'],
                    'scale' => 6,
                    'attr' => [
                        'min' => -90,
                        'max' => 90,
                        'step' => 0.000001,
                    ],
                ]
            )
            ->add(
                'longitude',
                NumberType::class,
                [
                    'label' => /** @Desc("Longitude") */ 'content.field_type.ibexa_gmap_location.longitude',
                    'required' => $options['required'],
                    'scale' => 6,
                    'attr' => [
                        'min' => -90,
                        'max' => 90,
                        'step' => 0.000001,
                    ],
                ]
            )
            ->add(
                'address',
                TextType::class,
                [
                    'label' => /** @Desc("Address") */ 'content.field_type.ibexa_gmap_location.address',
                    'required' => false,
                    'empty_data' => '',
                ]
            )
            ->addModelTransformer(
                new FieldValueTransformer(
                    $this->fieldTypeService->getFieldType('ibexa_gmap_location')
                )
            );
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->children['latitude']->vars['type'] = 'number';
        $view->children['longitude']->vars['type'] = 'number';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['translation_domain' => 'ibexa_content_forms_fieldtype']);
    }
}
