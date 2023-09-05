<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\FieldType;

use Ibexa\ContentForms\Data\User\UserAccountFieldData;
use Ibexa\ContentForms\Form\Type\SwitcherType;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAccountFieldType extends AbstractType
{
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'ezplatform_fieldtype_ezuser';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isUpdateForm = 'update' === $options['intent'];

        $builder
            ->add('username', TextType::class, [
                'label' => /** @Desc("Username") */ 'content.field_type.ezuser.username',
                'required' => true,
                'attr' => $isUpdateForm ? ['readonly' => 'readonly'] : [],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => !$isUpdateForm,
                'invalid_message' => /** @Desc("Passwords do not match.") */ 'content.field_type.passwords_must_match',
                'first_options' => ['label' => /** @Desc("Password") */ 'content.field_type.ezuser.password'],
                'second_options' => ['label' => /** @Desc("Confirm password") */ 'content.field_type.ezuser.password_confirm'],
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => /** @Desc("Email") */ 'content.field_type.ezuser.email',
                'attr' => [
                    'readonly' => $options['intent'] === 'invitation',
                ],
            ]);

        if (in_array($options['intent'], ['create', 'update'], true)) {
            $builder->add('enabled', SwitcherType::class, [
                'required' => false,
                'label' => /** @Desc("Enabled") */ 'content.field_type.ezuser.enabled',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => UserAccountFieldData::class,
                'translation_domain' => 'ibexa_content_forms_fieldtype',
            ])
            ->setRequired(['intent'])
            ->setAllowedValues('intent', ['register', 'create', 'update', 'invitation']);
    }
}

class_alias(UserAccountFieldType::class, 'EzSystems\EzPlatformContentForms\Form\Type\FieldType\UserAccountFieldType');
