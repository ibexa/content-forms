<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\FieldType\Author;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Combined Type for ezauthor.
 */
class AuthorCollectionType extends AbstractType
{
    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ezauthor_authors';
    }

    #[Override]
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

    #[Override]
    public function getParent(): ?string
    {
        return CollectionType::class;
    }
}
