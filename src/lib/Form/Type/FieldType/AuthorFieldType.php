<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\FieldType;

use Ibexa\ContentForms\Form\Type\FieldType\Author\AuthorCollectionType;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Core\FieldType\Author\Author;
use Ibexa\Core\FieldType\Author\Type as AuthorType;
use Ibexa\Core\FieldType\Author\Value;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form Type representing ibexa_author field type.
 */
final class AuthorFieldType extends AbstractType
{
    private int $defaultAuthor;

    public function __construct(private readonly Repository $repository)
    {
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ibexa_author';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->defaultAuthor = $options['default_author'];

        $builder
            ->add('authors', AuthorCollectionType::class)
            ->addViewTransformer($this->getViewTransformer($options['creator']))
            ->addEventListener(FormEvents::POST_SET_DATA, [$this, 'filterOutEmptyAuthors']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr']['default-author'] = $options['default_author'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Value::class,
            'default_author' => AuthorType::DEFAULT_VALUE_EMPTY,
            'creator' => null,
        ])->setAllowedTypes('default_author', 'integer')
          ->setAllowedTypes('creator', ['null', User::class]);
    }

    /**
     * Returns a view transformer which handles empty row needed to display add/remove buttons.
     */
    public function getViewTransformer(?User $creator = null): DataTransformerInterface
    {
        return new CallbackTransformer(function (Value $value) use ($creator): Value {
            if (0 === $value->authors->count()) {
                if ($this->defaultAuthor === AuthorType::DEFAULT_CURRENT_USER) {
                    $value->authors->append($creator !== null ? $this->userToAuthor($creator) : $this->fetchLoggedAuthor());
                } else {
                    $value->authors->append(new Author());
                }
            }

            return $value;
        }, static function (Value $value): Value {
            return $value;
        });
    }

    public function filterOutEmptyAuthors(FormEvent $event): void
    {
        $value = $event->getData();

        $value->authors->exchangeArray(
            array_filter(
                $value->authors->getArrayCopy(),
                static function (Author $author): bool {
                    return !empty($author->email) || !empty($author->name);
                }
            )
        );
    }

    private function fetchLoggedAuthor(): Author
    {
        try {
            $permissionResolver = $this->repository->getPermissionResolver();
            $userService = $this->repository->getUserService();
            $loggedUserId = $permissionResolver->getCurrentUserReference()->getUserId();
            $loggedUserData = $userService->loadUser($loggedUserId);

            return $this->userToAuthor($loggedUserData);
        } catch (NotFoundException) {
            //Do nothing
        }

        return new Author();
    }

    private function userToAuthor(User $creator): Author
    {
        $author = new Author();

        $author->name = $creator->getName();
        $author->email = $creator->email;

        return $author;
    }
}
