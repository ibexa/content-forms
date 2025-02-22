<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\Content;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentDraftCreateType extends AbstractType
{
    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'ezplatform_content_forms_content_draft_create';
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'contentId',
                TextType::class,
                [
                    'label' => 'Content Id',
                    'required' => true,
                ]
            )
            ->add(
                'fromVersionNo',
                TextType::class,
                [
                    'label' => 'From version',
                    'required' => false,
                ]
            )
            ->add(
                'fromLanguage',
                TextType::class,
                [
                    'label' => 'From language',
                    'required' => false,
                ]
            )
            ->add(
                'toLanguage',
                TextType::class,
                [
                    'label' => 'To language',
                    'required' => false,
                ]
            )
            ->add(
                'createDraft',
                SubmitType::class,
                ['label' => 'Create and edit draft']
            );
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['translation_domain' => 'ibexa_content_forms_content']);
    }
}
