<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\FieldType;

use Ibexa\ContentForms\FieldType\DataTransformer\RelationValueTransformer;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Core\FieldType\Relation\Value;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form Type representing ezobjectrelation field type.
 */
class RelationFieldType extends AbstractType
{
    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     */
    public function __construct(
        private ContentService $contentService,
        private ContentTypeService $contentTypeService
    ) {
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ezobjectrelation';
    }

    #[Override]
    public function getParent(): ?string
    {
        return IntegerType::class;
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new RelationValueTransformer());
    }

    #[Override]
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['relations'] = [];
        $view->vars['default_location'] = $options['default_location'];
        $view->vars['root_default_location'] = $options['root_default_location'];

        /** @var \Ibexa\Core\FieldType\Relation\Value $data */
        $data = $form->getData();

        if (!$data instanceof Value || null === $data->destinationContentId) {
            return;
        }
        $contentId = $data->destinationContentId;
        $contentInfo = null;
        $contentType = null;
        $unauthorized = false;

        try {
            $contentInfo = $this->contentService->loadContentInfo($contentId);
            $contentType = $this->contentTypeService->loadContentType($contentInfo->contentTypeId);
        } catch (UnauthorizedException $e) {
            $unauthorized = true;
        }

        $view->vars['relations'][$data->destinationContentId] = [
            'contentInfo' => $contentInfo,
            'contentType' => $contentType,
            'unauthorized' => $unauthorized,
            'contentId' => $contentId,
        ];
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => [
                'min' => 1,
                'step' => 1,
            ],
            'default_location' => null,
            'root_default_location' => null,
            'location' => null,
        ]);

        $resolver->setAllowedTypes('default_location', ['null', Location::class]);
        $resolver->setAllowedTypes('root_default_location', ['null', 'bool']);
        $resolver->setAllowedTypes('location', ['null', Location::class]);
    }
}
