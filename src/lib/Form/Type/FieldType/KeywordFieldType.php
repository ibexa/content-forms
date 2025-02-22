<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\FieldType;

use Ibexa\ContentForms\FieldType\DataTransformer\KeywordValueTransformer;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form Type representing ezkeyword field type.
 */
class KeywordFieldType extends AbstractType
{
    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ezkeyword';
    }

    #[Override]
    public function getParent(): ?string
    {
        return TextType::class;
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new KeywordValueTransformer());
    }
}
