<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\Content;

use Ibexa\ContentForms\Form\EventSubscriber\SuppressValidationSubscriber;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentStruct;
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
class ContentEditType extends AbstractType
{
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'ezplatform_content_forms_content_edit';
    }

    public function getParent()
    {
        return BaseContentType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('publish', SubmitType::class, ['label' => 'Publish'])
            ->add('publishAndEdit', SubmitType::class, ['label' => 'Publish and edit']);

        if (!$options['drafts_enabled']) {
            return;
        }

        $builder
            ->add('saveDraft', SubmitType::class, [
                'label' => /** @Desc("Save draft") */ 'save_draft',
                'attr' => ['formnovalidate' => 'formnovalidate'],
            ])
            ->add('saveDraftAndClose', SubmitType::class, [
                'label' => /** @Desc("Save draft and close") */ 'save_draft_and_close',
                'attr' => ['formnovalidate' => 'formnovalidate'],
            ])
            ->add('cancel', SubmitType::class, [
                'label' => /** @Desc("Cancel") */ 'cancel',
                'attr' => ['formnovalidate' => 'formnovalidate'],
            ]);

        if ($options['autosave_enabled']) {
            $builder->add('autosave', SubmitType::class, [
                'label' => /** @Desc("Autosave") */ 'autosave',
                'attr' => [
                    'hidden' => true,
                    'formnovalidate' => 'formnovalidate',
                ],
                'translation_domain' => 'ibexa_content_edit',
            ]);
        }

        $builder->addEventSubscriber(new SuppressValidationSubscriber());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'location' => null,
                'content' => null,
                'contentCreateStruct' => null,
                'contentUpdateStruct' => null,
                'drafts_enabled' => false,
                'autosave_enabled' => true,
                'data_class' => ContentStruct::class,
                'translation_domain' => 'ibexa_content_forms_content',
                'intent' => 'update',
            ]);
    }
}

class_alias(ContentEditType::class, 'EzSystems\EzPlatformContentForms\Form\Type\Content\ContentEditType');
