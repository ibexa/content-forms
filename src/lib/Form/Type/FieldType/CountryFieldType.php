<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\FieldType;

use Ibexa\ContentForms\FieldType\DataTransformer\MultipleCountryValueTransformer;
use Ibexa\ContentForms\FieldType\DataTransformer\SingleCountryValueTransformer;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form Type representing ezcountry field type.
 */
class CountryFieldType extends AbstractType
{
    /**
     * @param array $countriesInfo
     */
    public function __construct(protected array $countriesInfo)
    {
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ezcountry';
    }

    #[Override]
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(
            $options['multiple']
                ? new MultipleCountryValueTransformer($this->countriesInfo)
                : new SingleCountryValueTransformer($this->countriesInfo)
        );
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'expanded' => false,
            'choices' => $this->getCountryChoices($this->countriesInfo),
        ]);
    }

    /**
     * @return mixed[]
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
