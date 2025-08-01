<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\FieldDefinition\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PasswordConstraintCheckboxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer('boolval', 'boolval'));
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['label'] = 'field_definition.ibexa_user.' . $this->toSnakeCase($view->vars['name']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
        ]);
    }

    public function getParent(): string
    {
        return CheckboxType::class;
    }

    private function toSnakeCase(string $string): string
    {
        return strtolower(preg_replace('/[A-Z]/', '_\\0', lcfirst($string)));
    }
}
