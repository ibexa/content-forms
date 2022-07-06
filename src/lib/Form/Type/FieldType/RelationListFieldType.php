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
 * Form Type representing ezobjectrelationlist field type.
 */
class RelationListFieldType extends AbstractType
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    private $contentTypeService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentService $contentService
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     */
    public function __construct(ContentService $contentService, ContentTypeService $contentTypeService)
    {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'ezplatform_fieldtype_ezobjectrelationlist';
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new RelationListValueTransformer());
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
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
            } catch (UnauthorizedException $e) {
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

    /**
     * {@inheritdoc}
     */
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

class_alias(RelationListFieldType::class, 'EzSystems\EzPlatformContentForms\Form\Type\FieldType\RelationListFieldType');
