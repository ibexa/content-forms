<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\FieldType;

use Ibexa\ContentForms\FieldType\DataTransformer\RelationListValueTransformer;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Core\FieldType\RelationList\Value;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form Type representing ibexa_object_relation_list field type.
 */
final class RelationListFieldType extends AbstractType
{
    public function __construct(
        private readonly ContentService $contentService,
        private readonly ContentTypeService $contentTypeService
    ) {
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ibexa_object_relation_list';
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new RelationListValueTransformer());
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['relations'] = [];
        $view->vars['default_location'] = $options['default_location'];
        $view->vars['root_default_location'] = $options['root_default_location'];

        /** @var \Ibexa\Core\FieldType\RelationList\Value $data */
        $data = $form->getData();

        if (!$data instanceof Value) {
            return;
        }

        foreach ($data->destinationContentIds as $contentId) {
            $contentInfo = null;
            $contentType = null;
            $unauthorized = false;

            try {
                $contentInfo = $this->contentService->loadContentInfo($contentId);
                $contentType = $this->contentTypeService->loadContentType($contentInfo->contentTypeId);
            } catch (UnauthorizedException) {
                $unauthorized = true;
            }

            $view->vars['relations'][$contentId] = [
                'contentInfo' => $contentInfo,
                'contentType' => $contentType,
                'unauthorized' => $unauthorized,
                'contentId' => $contentId,
            ];
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'default_location' => null,
            'root_default_location' => null,
            'location' => null,
        ]);

        $resolver->setAllowedTypes('default_location', ['null', Location::class]);
        $resolver->setAllowedTypes('root_default_location', ['null', 'bool']);
        $resolver->setAllowedTypes('location', ['null', Location::class]);
    }
}
