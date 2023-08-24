<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\User;

use Ibexa\ContentForms\Form\EventSubscriber\SuppressValidationSubscriber;
use Ibexa\ContentForms\Form\EventSubscriber\UserFieldsSubscriber;
use Ibexa\ContentForms\Form\Type\Content\BaseContentType;
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
class BaseUserType extends AbstractType
{
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'ezplatform_content_forms_user';
    }

    public function getParent()
    {
        return BaseContentType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cancel', SubmitType::class, [
                'label' => /** @Desc("Cancel") */ 'user.cancel',
                'attr' => ['formnovalidate' => 'formnovalidate'],
            ])
            ->addEventSubscriber(new UserFieldsSubscriber())
            ->addEventSubscriber(new SuppressValidationSubscriber());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'translation_domain' => 'ibexa_content_forms_user',
            ])
            ->setRequired([
                'languageCode',
                'intent',
            ])
            ->setAllowedValues('intent', ['update', 'create', 'register']);
    }
}

class_alias(BaseUserType::class, 'EzSystems\EzPlatformContentForms\Form\Type\User\BaseUserType');
