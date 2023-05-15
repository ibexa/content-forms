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
 * Form Type representing ezauthor field type.
 */
class AuthorFieldType extends AbstractType
{
    /** @var \Ibexa\Contracts\Core\Repository\Repository */
    private $repository;

    /** @var int */
    private $defaultAuthor;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'ezplatform_fieldtype_ezauthor';
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->defaultAuthor = $options['default_author'];

        $builder
            ->add('authors', AuthorCollectionType::class, [])
            ->addViewTransformer($this->getViewTransformer())
            ->addEventListener(FormEvents::POST_SET_DATA, [$this, 'filterOutEmptyAuthors']);
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attr']['default-author'] = $options['default_author'];
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Value::class,
            'default_author' => AuthorType::DEFAULT_VALUE_EMPTY,
        ])->setAllowedTypes('default_author', 'integer');
    }

    /**
     * Returns a view transformer which handles empty row needed to display add/remove buttons.
     *
     * @return \Symfony\Component\Form\DataTransformerInterface
     */
    public function getViewTransformer(): DataTransformerInterface
    {
        return new CallbackTransformer(function (Value $value) {
            if (0 === $value->authors->count()) {
                if ($this->defaultAuthor === AuthorType::DEFAULT_CURRENT_USER) {
                    $value->authors->append($this->fetchLoggedAuthor());
                } else {
                    $value->authors->append(new Author());
                }
            }

            return $value;
        }, static function (Value $value) {
            return $value;
        });
    }

    /**
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function filterOutEmptyAuthors(FormEvent $event)
    {
        $value = $event->getData();

        $value->authors->exchangeArray(
            array_filter(
                $value->authors->getArrayCopy(),
                static function (Author $author) {
                    return !empty($author->email) || !empty($author->name);
                }
            )
        );
    }

    /**
     * Returns currently logged user data, or empty Author object if none was found.
     *
     * @return \Ibexa\Core\FieldType\Author\Author
     */
    private function fetchLoggedAuthor(): Author
    {
        $author = new Author();

        try {
            $permissionResolver = $this->repository->getPermissionResolver();
            $userService = $this->repository->getUserService();
            $loggedUserId = $permissionResolver->getCurrentUserReference()->getUserId();
            $loggedUserData = $userService->loadUser($loggedUserId);

            $author->name = $loggedUserData->getName();
            $author->email = $loggedUserData->email;
        } catch (NotFoundException $e) {
            //Do nothing
        }

        return $author;
    }
}

class_alias(AuthorFieldType::class, 'EzSystems\EzPlatformContentForms\Form\Type\FieldType\AuthorFieldType');
