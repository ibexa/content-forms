<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Suppresses validation on cancel button submit.
 */
class SuppressValidationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SUBMIT => [
                ['suppressValidationOnCancel', 900],
                ['suppressValidationOnSaveDraft', 900],
                ['suppressValidationOnAutosaveDraft', 900],
            ],
        ];
    }

    public function suppressValidationOnCancel(FormEvent $event)
    {
        $form = $event->getForm();

        if ($form->get('cancel')->isClicked()) {
            $event->stopPropagation();
        }
    }

    public function suppressValidationOnSaveDraft(PostSubmitEvent $event)
    {
        $form = $event->getForm();

        if (
            ($form->has('saveDraft') && $form->get('saveDraft')->isClicked())
            || ($form->has('saveDraftAndClose') && $form->get('saveDraftAndClose')->isClicked())
        ) {
            $event->stopPropagation();
        }
    }

    public function suppressValidationOnAutosaveDraft(PostSubmitEvent $event)
    {
        $form = $event->getForm();

        if ($form->has('autosave') && $form->get('autosave')->isClicked()) {
            $event->stopPropagation();
        }
    }
}

class_alias(SuppressValidationSubscriber::class, 'EzSystems\EzPlatformContentForms\Form\EventSubscriber\SuppressValidationSubscriber');
