<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\FieldType;

use Ibexa\ContentForms\ConfigResolver\MaxUploadSize;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Parent Form Type for binary file based field types.
 */
class BinaryBaseFieldType extends AbstractType
{
    /** @var \Ibexa\ContentForms\ConfigResolver\MaxUploadSize */
    private $maxUploadSize;

    public function __construct(MaxUploadSize $maxUploadSize)
    {
        $this->maxUploadSize = $maxUploadSize;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'remove',
                CheckboxType::class,
                [
                    'label' => /** @Desc("Remove") */ 'content.field_type.binary_base.remove',
                ]
            )
            ->add(
                'file',
                FileType::class,
                [
                    'label' => /** @Desc("File") */ 'content.field_type.binary_base.file',
                    'required' => $options['required'],
                    'constraints' => [
                        new Assert\File([
                            'maxSize' => $this->maxUploadSize->get(),
                        ]),
                    ],
                ]
            );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['max_upload_size'] = $this->maxUploadSize->get();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'ibexa_content_forms_fieldtype']);
    }
}

class_alias(BinaryBaseFieldType::class, 'EzSystems\EzPlatformContentForms\Form\Type\FieldType\BinaryBaseFieldType');
