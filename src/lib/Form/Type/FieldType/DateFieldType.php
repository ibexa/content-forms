<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\FieldType;

use Ibexa\ContentForms\FieldType\DataTransformer\DateValueTransformer;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Form Type representing ezdate field type.
 */
class DateFieldType extends AbstractType
{
    private const array EDIT_VIEWS = ['ibexa.content.draft.edit', 'ibexa.content.translate', 'ibexa.content.translate_with_location', 'ibexa.user.update'];

    public function __construct(private RequestStack $requestStack)
    {
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ezdate';
    }

    #[Override]
    public function getParent(): ?string
    {
        return IntegerType::class;
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addModelTransformer(new DateValueTransformer());
    }

    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $view->vars['isEditView'] = in_array($request->attributes->get('_route'), self::EDIT_VIEWS);
    }
}
