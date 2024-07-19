<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\User;

use Ibexa\ContentForms\Data\User\UserUpdateData;
use Ibexa\Contracts\Core\Repository\Values\User\UserUpdateStruct;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for content edition (create/update).
 * Underlying data will be either \Ibexa\ContentForms\Data\Content\ContentCreateData or \Ibexa\ContentForms\Data\Content\ContentUpdateData
 * depending on the context (create or update).
 */
class UserUpdateType extends AbstractType
{
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_content_forms_user_update';
    }

    public function getParent(): ?string
    {
        return BaseUserType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('update', SubmitType::class, ['label' => /** @Desc("Update") */ 'user.update']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'location' => null,
                'content' => null,
                'data_class' => UserUpdateData::class,
                'intent' => 'update',
                'translation_domain' => 'ibexa_content_forms_user',
            ])
            ->setAllowedTypes('struct', ['null', UserUpdateStruct::class]);
    }
}
