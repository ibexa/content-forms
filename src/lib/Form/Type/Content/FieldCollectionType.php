<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\Content;

use Ibexa\ContentForms\Event\ContentCreateFieldOptionsEvent;
use Ibexa\ContentForms\Event\ContentFormEvents;
use Ibexa\ContentForms\Event\ContentUpdateFieldOptionsEvent;
use Ibexa\ContentForms\Event\UserCreateFieldOptionsEvent;
use Ibexa\ContentForms\Event\UserUpdateFieldOptionsEvent;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\Values\User\UserUpdateStruct;
use Ibexa\Core\Repository\Values\Content\ContentCreateStruct;
use Ibexa\Core\Repository\Values\Content\ContentUpdateStruct;
use Ibexa\Core\Repository\Values\User\UserCreateStruct;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FieldCollectionType extends CollectionType
{
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ) {
        parent::buildForm($builder, $options);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $data = $event->getData();

            foreach ($form as $name => $child) {
                $form->remove($name);
            }

            // Then add all rows again in the correct order
            foreach ($data as $name => $entryData) {
                $entryOptions = array_replace([
                    'property_path' => '[' . $name . ']',
                ], $options['entry_options']);

                $entryOptions = $this->dispatchFieldOptionsEvent($entryData, $entryOptions, $form);

                $form->add($name, $options['entry_type'], $entryOptions);
            }
        });
    }

    private function isContentCreate(array $entryOptions): bool
    {
        return !empty($entryOptions['struct']) && $entryOptions['struct'] instanceof ContentCreateStruct;
    }

    private function isContentUpdate(array $entryOptions): bool
    {
        return !empty($entryOptions['struct']) && $entryOptions['struct'] instanceof ContentUpdateStruct;
    }

    /**
     * @param array<string, mixed> $entryOptions
     */
    private function isUserCreate(array $entryOptions): bool
    {
        return !empty($entryOptions['struct']) && $entryOptions['struct'] instanceof UserCreateStruct;
    }

    /**
     * @param array<string, mixed> $entryOptions
     */
    private function isUserUpdate(array $entryOptions): bool
    {
        return !empty($entryOptions['struct']) && $entryOptions['struct'] instanceof UserUpdateStruct;
    }

    /**
     * @param array<string, mixed> $entryOptions
     *
     * @return array<string, mixed>
     */
    private function dispatchFieldOptionsEvent(
        FieldData $entryData,
        array $entryOptions,
        FormInterface $form
    ): array {
        if ($this->isContentUpdate($entryOptions)) {
            /** @var \Ibexa\ContentForms\Event\ContentUpdateFieldOptionsEvent $contentUpdateFieldOptionsEvent */
            $contentUpdateFieldOptionsEvent = $this->eventDispatcher->dispatch(
                new ContentUpdateFieldOptionsEvent(
                    $entryOptions['content'],
                    $entryOptions['struct'],
                    $form,
                    $entryData,
                    $entryOptions
                ),
                ContentFormEvents::CONTENT_EDIT_FIELD_OPTIONS
            );

            $entryOptions = $contentUpdateFieldOptionsEvent->getOptions();
        } elseif ($this->isContentCreate($entryOptions)) {
            /** @var \Ibexa\ContentForms\Event\ContentCreateFieldOptionsEvent $contentUpdateFieldOptionsEvent */
            $contentCreateFieldOptionsEvent = $this->eventDispatcher->dispatch(
                new ContentCreateFieldOptionsEvent(
                    $entryOptions['struct'],
                    $form,
                    $entryData,
                    $entryOptions
                ),
                ContentFormEvents::CONTENT_CREATE_FIELD_OPTIONS
            );

            $entryOptions = $contentCreateFieldOptionsEvent->getOptions();
        } elseif ($this->isUserCreate($entryOptions)) {
            /** @var \Ibexa\ContentForms\Event\UserCreateFieldOptionsEvent $userCreateFieldOptionsEvent */
            $userCreateFieldOptionsEvent = $this->eventDispatcher->dispatch(
                new UserCreateFieldOptionsEvent(
                    $entryOptions['struct'],
                    $form,
                    $entryData,
                    $entryOptions
                ),
                ContentFormEvents::USER_CREATE_FIELD_OPTIONS
            );

            $entryOptions = $userCreateFieldOptionsEvent->getOptions();
        } elseif ($this->isUserUpdate($entryOptions)) {
            /** @var \Ibexa\ContentForms\Event\UserUpdateFieldOptionsEvent $userUpdateFieldOptionsEvent */
            $userUpdateFieldOptionsEvent = $this->eventDispatcher->dispatch(
                new UserUpdateFieldOptionsEvent(
                    $entryOptions['content'],
                    $entryOptions['struct'],
                    $form,
                    $entryData,
                    $entryOptions
                ),
                ContentFormEvents::USER_EDIT_FIELD_OPTIONS
            );

            $entryOptions = $userUpdateFieldOptionsEvent->getOptions();
        }

        return $entryOptions;
    }
}

class_alias(FieldCollectionType::class, 'EzSystems\EzPlatformContentForms\Form\Type\Content\FieldCollectionType');
