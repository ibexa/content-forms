<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\User;

use Ibexa\ContentForms\Data\User\UserUpdateData;
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

    public function getBlockPrefix()
    {
        return 'ezplatform_content_forms_user_update';
    }

    public function getParent()
    {
        return BaseUserType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('update', SubmitType::class, ['label' => /** @Desc("Update") */ 'user.update']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => UserUpdateData::class,
                'intent' => 'update',
                'translation_domain' => 'ezplatform_content_forms_user',
            ]);
    }
}

class_alias(UserUpdateType::class, 'EzSystems\EzPlatformContentForms\Form\Type\User\UserUpdateType');
