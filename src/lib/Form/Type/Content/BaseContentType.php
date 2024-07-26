<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\Content;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentStruct;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentUpdateStruct;
use JMS\TranslationBundle\Annotation\Desc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Base Type used on User or Content create/edit forms.
 */
class BaseContentType extends AbstractType
{
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'ezplatform_content_forms_content';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fieldsData', FieldCollectionType::class, [
                'entry_type' => ContentFieldType::class,
                'label' => /** @Desc("Fields") */ 'ezplatform.content_forms.content.fields',
                'entry_options' => [
                    'languageCode' => $options['languageCode'],
                    'mainLanguageCode' => $options['mainLanguageCode'],
                    'location' => $options['location'] ?? null,
                    'content' => $options['content'] ?? null,
                    'contentCreateStruct' => $options['contentCreateStruct'] ?? null,
                    'contentUpdateStruct' => $options['contentUpdateStruct'] ?? null,
                    'struct' => $options['struct'],
                ],
                'translation_domain' => 'ibexa_content_forms_content',
            ])
            ->add('redirectUrlAfterPublish', HiddenType::class, [
                'required' => false,
                'mapped' => false,
            ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['languageCode'] = $options['languageCode'];
        $view->vars['mainLanguageCode'] = $options['mainLanguageCode'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['languageCode', 'mainLanguageCode', 'struct'])
            ->setDefault('struct', static function (Options $options, ?ContentStruct $value) {
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
                'translation_domain' => 'ibexa_content_forms_content',
                'contentCreateStruct' => null,
                'contentUpdateStruct' => null,
            ])
            ->setAllowedTypes(
                'struct',
                [
                    'null',
                    ContentCreateStruct::class,
                    ContentUpdateStruct::class,
                ],
            )
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
}
