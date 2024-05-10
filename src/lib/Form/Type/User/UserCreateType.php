<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\User;

use Ibexa\ContentForms\Data\User\UserCreateData;
use Ibexa\Contracts\Core\Repository\Values\User\UserCreateStruct;
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
class UserCreateType extends AbstractType
{
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'ezplatform_content_forms_user_create';
    }

    public function getParent()
    {
        return BaseUserType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('create', SubmitType::class, ['label' => /** @Desc("Create") */ 'user.create']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('struct')
            ->setDefaults([
                'data_class' => UserCreateData::class,
                'intent' => 'create',
                'translation_domain' => 'ibexa_content_forms_user',
            ])
            ->setAllowedTypes('struct', UserCreateStruct::class);
    }
}

class_alias(UserCreateType::class, 'EzSystems\EzPlatformContentForms\Form\Type\User\UserCreateType');
