<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\FieldType\Author;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Combined Type for ezauthor.
 */
class AuthorCollectionType extends AbstractType
{
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ezauthor_authors';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_add' => true,
            'allow_delete' => true,
            'entry_type' => AuthorEntryType::class,
            'prototype' => true,
            'prototype_name' => '__index__',
        ]);
    }

    public function getParent(): ?string
    {
        return CollectionType::class;
    }
}
