<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type;

use Ibexa\ContentForms\Form\Transformer\RelationTransformer;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RelationType extends AbstractType
{
    public const LOCATION_DEFAULT = 0;
    public const LOCATION_BROWSE = 1;
    public const LOCATION_SELF = -1;

    private LocationService $locationService;

    private TranslatorInterface $translator;

    public function __construct(
        LocationService $locationService,
        TranslatorInterface $translator
    ) {
        $this->locationService = $locationService;
        $this->translator = $translator;
    }

    public function getBlockPrefix()
    {
        return 'ibexa_form_type_relation';
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ) {
        $builder
            ->add('location', LocationType::class)
            ->add('location_type', LocationChoiceType::class, [
                'multiple' => false,
                'expanded' => true,
                'label' => /** @Desc("Select Starting Location") */ 'content_forms.relation.location_type',
                'choices' => [
                    /** @Desc("Default") */
                    'content_forms.relation.location_type.default' => self::LOCATION_DEFAULT,
                    /** @Desc("Browse") */
                    'content_forms.relation.location_type.browse' => self::LOCATION_BROWSE,
                    /** @Desc("Content location") */
                    'content_forms.relation.location_type.self' => self::LOCATION_SELF,
                ],
            ]);

        $builder->addModelTransformer(new RelationTransformer());
    }

    public function buildView(
        FormView $view,
        FormInterface $form,
        array $options
    ) {
        $view->vars['destination_location'] = null;
        $value = $form->getData();

        if (!empty($value)) {
            try {
                $view->vars['destination_location'] = $this->locationService->loadLocation(
                    (int)$value
                );
            } catch (NotFoundException | UnauthorizedException $e) {
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'translation_domain' => 'ibexa_content_forms_fieldtype',
            ]);
    }
}
