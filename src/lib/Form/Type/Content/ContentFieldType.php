<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\Content;

use Ibexa\ContentForms\FieldType\FieldTypeFormMapperDispatcherInterface;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentStruct;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentUpdateStruct;
use Ibexa\Contracts\Core\Repository\Values\User\UserCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\User\UserUpdateStruct;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ContentFieldType extends AbstractType
{
    public function __construct(
        private FieldTypeFormMapperDispatcherInterface $fieldTypeFormMapper
    ) {
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_content_forms_content_field';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['languageCode', 'mainLanguageCode', 'struct'])
            ->setDefault('struct', static function (Options $options, ?ContentStruct $value): ?ContentStruct {
                if ($value !== null) {
                    return $value;
                }

                trigger_deprecation(
                    'ibexa/content-forms',
                    'v4.6',
                    'The option "struct" with null value is deprecated and will be required in v5.0.'
                );

                return $options['contentUpdateStruct']
                    ?? $options['contentCreateStruct']
                    ?? null;
            })
            ->setDefaults([
                'content' => null,
                'location' => null,
                'contentCreateStruct' => null,
                'contentUpdateStruct' => null,
                'data_class' => FieldData::class,
                'translation_domain' => 'ibexa_content_forms_content',
            ])
            ->setAllowedTypes(
                'struct',
                [
                    'null',
                    ContentCreateStruct::class,
                    ContentUpdateStruct::class,
                    UserCreateStruct::class,
                    UserUpdateStruct::class,
                ],
            )
            ->setAllowedTypes('contentCreateStruct', ['null', ContentCreateStruct::class])
            ->setAllowedTypes('contentUpdateStruct', ['null', ContentUpdateStruct::class])
            ->setDeprecated(
                'contentCreateStruct',
                'ibexa/content-forms',
                'v4.6.4',
                'The option "%name%" is deprecated, use "struct" instead.'
            )
            ->setDeprecated(
                'contentUpdateStruct',
                'ibexa/content-forms',
                'v4.6.4',
                'The option "%name%" is deprecated, use "struct" instead.'
            );
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['location'] = $options['location'];
        $view->vars['languageCode'] = $options['languageCode'];
        $view->vars['mainLanguageCode'] = $options['mainLanguageCode'];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $this->fieldTypeFormMapper->map($event->getForm(), $event->getData());
        });
    }
}
