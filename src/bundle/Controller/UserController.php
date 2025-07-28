<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\ContentForms\Controller;

use Ibexa\Bundle\Core\Controller;
use Ibexa\ContentForms\Data\Mapper\UserCreateMapper;
use Ibexa\ContentForms\Data\Mapper\UserUpdateMapper;
use Ibexa\ContentForms\Form\ActionDispatcher\ActionDispatcherInterface;
use Ibexa\ContentForms\Form\Type\User\UserCreateType;
use Ibexa\ContentForms\Form\Type\User\UserUpdateType;
use Ibexa\ContentForms\User\View\UserCreateView;
use Ibexa\ContentForms\User\View\UserUpdateView;
use Ibexa\Contracts\ContentForms\Content\Form\Provider\GroupedContentFormFieldsProviderInterface;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Core\Base\Exceptions\UnauthorizedException as CoreUnauthorizedException;
use Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UserController extends Controller
{
    public function __construct(
        private readonly ContentTypeService $contentTypeService,
        private readonly UserService $userService,
        private readonly LocationService $locationService,
        private readonly LanguageService $languageService,
        private readonly ActionDispatcherInterface $userActionDispatcher,
        private readonly PermissionResolver $permissionResolver,
        private readonly UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider,
        private readonly GroupedContentFormFieldsProviderInterface $groupedContentFormFieldsProvider,
        private readonly ContentService $contentService
    ) {
    }

    /**
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentType
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\NoSuchOptionException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function createAction(
        string $contentTypeIdentifier,
        string $language,
        int $parentLocationId,
        Request $request
    ): Response|UserCreateView {
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier(
            $contentTypeIdentifier,
            $this->userLanguagePreferenceProvider->getPreferredLanguages()
        );
        $location = $this->locationService->loadLocation($parentLocationId);
        $language = $this->languageService->loadLanguage($language);
        $languageCode = $language->getLanguageCode();
        $parentGroup = $this->userService->loadUserGroup($location->getContentId());

        $data = (new UserCreateMapper())->mapToFormData($contentType, [$parentGroup], [
            'mainLanguageCode' => $languageCode,
        ]);
        $form = $this->createForm(UserCreateType::class, $data, [
            'languageCode' => $languageCode,
            'mainLanguageCode' => $languageCode,
            'struct' => $data,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && null !== $form->getClickedButton()) {
            $this->userActionDispatcher->dispatchFormAction($form, $data, $form->getClickedButton()->getName());
            if ($response = $this->userActionDispatcher->getResponse()) {
                return $response;
            }
        }

        return new UserCreateView(
            null,
            [
                'form' => $form->createView(),
                'language' => $language,
                'parent_location' => $location,
                'content_type' => $contentType,
                'parent_group' => $parentGroup,
                'grouped_fields' => $this->groupedContentFormFieldsProvider->getGroupedFields(
                    $form->get('fieldsData')->all()
                ),
            ]
        );
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentType
     * @throws \Ibexa\Core\Base\Exceptions\UnauthorizedException
     */
    public function editAction(
        int $contentId,
        int $versionNo,
        string $language,
        Request $request
    ): Response|UserUpdateView {
        $user = $this->userService->loadUser($contentId);
        if (!$this->permissionResolver->canUser('content', 'edit', $user)) {
            throw new CoreUnauthorizedException('content', 'edit', ['userId' => $contentId]);
        }
        $contentType = $this->contentTypeService->loadContentType(
            $user->getContentInfo()->getContentType()->id,
            $this->userLanguagePreferenceProvider->getPreferredLanguages()
        );

        $userUpdate = (new UserUpdateMapper())->mapToFormData($user, $contentType, [
            'languageCode' => $language,
        ]);

        try {
            // assume main location if no location was provided
            $location = $this->locationService->loadLocation(
                (int)$user->getVersionInfo()->getContentInfo()->getMainLocationId()
            );
        } catch (UnauthorizedException $e) {
            // if no access to the main location assume content has multiple locations and first of them can be used
            $availableLocations = $this->locationService->loadLocations(
                $user->getVersionInfo()->getContentInfo()
            );
            $location = array_shift($availableLocations);
        }

        $form = $this->createForm(
            UserUpdateType::class,
            $userUpdate,
            [
                'location' => $location,
                'content' => $this->contentService->loadContent($contentId),
                'languageCode' => $language,
                'mainLanguageCode' => $user->getContentInfo()->getMainLanguageCode(),
                'struct' => $userUpdate,
            ],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && null !== $form->getClickedButton()) {
            $this->userActionDispatcher->dispatchFormAction($form, $userUpdate, $form->getClickedButton()->getName());
            if ($response = $this->userActionDispatcher->getResponse()) {
                return $response;
            }
        }

        $parentLocation = null;
        try {
            $parentLocation = $this->locationService->loadLocation($location->parentLocationId);
        } catch (UnauthorizedException) {
            //do nothing
        }

        return new UserUpdateView(
            null,
            [
                'form' => $form->createView(),
                'language_code' => $language,
                'language' => $this->languageService->loadLanguage($language),
                'content_type' => $contentType,
                'user' => $user,
                'location' => $location,
                'parent_location' => $parentLocation,
                'grouped_fields' => $this->groupedContentFormFieldsProvider->getGroupedFields(
                    $form->get('fieldsData')->all()
                ),
            ]
        );
    }
}
