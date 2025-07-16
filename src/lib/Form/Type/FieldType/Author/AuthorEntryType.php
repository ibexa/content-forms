<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\FieldType\Author;

use Ibexa\Core\FieldType\Author\Author;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Combined entry type for ibexa_author.
 */
final class AuthorEntryType extends AbstractType
{
    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

   public function getBlockPrefix(): string
   {
        return 'ezplatform_fieldtype_ibexa_author_authors_entry';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'id',
                HiddenType::class,
                [
                    'label' => false,
                ]
            )
            ->add(
                'name',
                TextType::class,
                [
                    'label' => /** @Desc("Name") */
                        'content.field_type.ibexa_author.name',
                    'required' => $options['required'],
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => /** @Desc("Email") */
                        'content.field_type.ibexa_author.email',
                    'required' => $options['required'],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Author::class,
            'translation_domain' => 'ibexa_content_forms_fieldtype',
        ]);
    }
}
