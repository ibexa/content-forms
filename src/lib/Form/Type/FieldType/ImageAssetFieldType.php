<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\FieldType;

use Ibexa\ContentForms\ConfigResolver\MaxUploadSize;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Core\FieldType\ImageAsset\AssetMapper;
use JMS\TranslationBundle\Annotation\Desc;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageAssetFieldType extends AbstractType
{
    public function __construct(
        private ContentService $contentService,
        private AssetMapper $assetMapper,
        private MaxUploadSize $maxUploadSize,
        private MimeTypesInterface $mimeTypes
    ) {
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ezimageasset';
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('destinationContentId', HiddenType::class)
            ->add(
                'remove',
                CheckboxType::class,
                [
                    'label' => /** @Desc("Remove") */ 'content.field_type.binary_base.remove',
                    'mapped' => false,
                ]
            )
            ->add(
                'file',
                FileType::class,
                [
                    'label' => /** @Desc("File") */ 'content.field_type.binary_base.file',
                    'required' => $options['required'],
                    'mapped' => false,
                ]
            )
            ->add(
                'alternativeText',
                TextType::class,
                [
                    'label' => /** @Desc("Alternative text") */ 'content.field_type.ezimageasset.alternative_text',
                    'block_prefix' => 'ibexa_fieldtype_ezimage_alternative_text',
                ]
            );
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['destination_content'] = null;

        if ($view->vars['value']['destinationContentId']) {
            try {
                $content = $this->contentService->loadContent(
                    (int)$view->vars['value']['destinationContentId']
                );

                if (!$content->contentInfo->isTrashed()) {
                    $view->vars['destination_content'] = $content;
                }
            } catch (NotFoundException | UnauthorizedException $exception) {
            }
        }

        $mimeTypes = $this->assetMapper
            ->getAssetFieldDefinition()
            ->getFieldSettings()['mimeTypes'] ?? [];

        if (!empty($mimeTypes)) {
            $view->vars['mime_types'] = $mimeTypes;
            $view->vars['image_extensions'] = $this->getMimeTypesExtensions($mimeTypes);
        }

        $view->vars['max_file_size'] = $this->getMaxFileSize();
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'ibexa_content_forms_fieldtype',
        ]);
    }

    /**
     * Returns max size of uploaded file in bytes.
     */
    private function getMaxFileSize(): float
    {
        $validatorConfiguration = $this->assetMapper
            ->getAssetFieldDefinition()
            ->getValidatorConfiguration();

        return (float)$validatorConfiguration['FileSizeValidator']['maxFileSize'] * 1024 * 1024;
    }

    /**
     * @param array<string> $mimeTypes
     *
     * @return array<string, array<string>>
     */
    private function getMimeTypesExtensions(array $mimeTypes): array
    {
        $extensions = [];
        foreach ($mimeTypes as $mimeType) {
            $extensions[$mimeType] = $this->mimeTypes->getExtensions($mimeType);
        }

        return $extensions;
    }
}
