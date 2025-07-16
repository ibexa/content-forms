<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\FieldType;

use Ibexa\ContentForms\FieldType\DataTransformer\MultipleCountryValueTransformer;
use Ibexa\ContentForms\FieldType\DataTransformer\SingleCountryValueTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form Type representing ibexa_country field type.
 */
final class CountryFieldType extends AbstractType
{
    public function __construct(private readonly array $countriesInfo)
    {
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ibexa_country';
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(
            $options['multiple']
                ? new MultipleCountryValueTransformer($this->countriesInfo)
                : new SingleCountryValueTransformer($this->countriesInfo)
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'expanded' => false,
            'choices' => $this->getCountryChoices($this->countriesInfo),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function getCountryChoices(array $countriesInfo): array
    {
        $choices = [];
        foreach ($countriesInfo as $country) {
            $choices[$country['Name']] = $country['Alpha2'];
        }

        return $choices;
    }
}
