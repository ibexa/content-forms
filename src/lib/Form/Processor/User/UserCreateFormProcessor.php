<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Processor\User;

use Ibexa\ContentForms\Data\User\UserCreateData;
use Ibexa\ContentForms\Event\ContentFormEvents;
use Ibexa\ContentForms\Event\FormActionEvent;
use Ibexa\Contracts\Core\Repository\UserService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Listens for and processes User create events.
 */
final readonly class UserCreateFormProcessor implements EventSubscriberInterface
{
    public function __construct(
        private UserService $userService,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContentFormEvents::USER_CREATE => ['processCreate', 20],
        ];
    }

    public function processCreate(FormActionEvent $event): void
    {
        $data = $event->getData();
        if (!$data instanceof UserCreateData) {
            return;
        }

        $form = $event->getForm();
        $languageCode = $form->getConfig()->getOption('languageCode');

        $this->setContentFields($data, $languageCode);
        $user = $this->userService->createUser($data, $data->getParentGroups());

        $redirectUrl = $form['redirectUrlAfterPublish']->getData() ?: $this->urlGenerator->generate(
            'ibexa.content.view',
            [
                'contentId' => $user->getUserId(),
                'locationId' => $user->getContentInfo()->getMainLocationId(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $event->setResponse(new RedirectResponse($redirectUrl));
    }

    private function setContentFields(UserCreateData $data, string $languageCode): void
    {
        foreach ($data->getFieldsData() as $fieldDefIdentifier => $fieldData) {
            $data->setField($fieldDefIdentifier, $fieldData->getValue(), $languageCode);
        }
    }
}
