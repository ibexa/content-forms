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

class UserController extends Controller
{
    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \Ibexa\Contracts\Core\Repository\UserService */
    private $userService;

    /** @var \Ibexa\Contracts\Core\Repository\LocationService */
    private $locationService;

    /** @var \Ibexa\Contracts\Core\Repository\LanguageService */
    private $languageService;

    /** @var \Ibexa\ContentForms\Form\ActionDispatcher\ActionDispatcherInterface */
    private $userActionDispatcher;

    /** @var \Ibexa\Contracts\Core\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \Ibexa\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface */
    private $userLanguagePreferenceProvider;

    /** @var \Ibexa\Contracts\ContentForms\Content\Form\Provider\GroupedContentFormFieldsProviderInterface */
    private $groupedContentFormFieldsProvider;

    private ContentService $contentService;

    public function __construct(
        ContentTypeService $contentTypeService,
        UserService $userService,
        LocationService $locationService,
        LanguageService $languageService,
        ActionDispatcherInterface $userActionDispatcher,
        PermissionResolver $permissionResolver,
        UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider,
        GroupedContentFormFieldsProviderInterface $groupedContentFormFieldsProvider,
        ContentService $contentService
    ) {
        $this->contentTypeService = $contentTypeService;
        $this->userService = $userService;
        $this->locationService = $locationService;
        $this->languageService = $languageService;
        $this->userActionDispatcher = $userActionDispatcher;
        $this->permissionResolver = $permissionResolver;
        $this->userLanguagePreferenceProvider = $userLanguagePreferenceProvider;
        $this->groupedContentFormFieldsProvider = $groupedContentFormFieldsProvider;
        $this->contentService = $contentService;
    }

    /**
     * Displays and processes a user creation form.
     *
     * @param string $contentTypeIdentifier ContentType id to create
     * @param string $language Language code to create the content in (eng-GB, ger-DE, ...))
     * @param int $parentLocationId Location the content should be a child of
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response|\Ibexa\ContentForms\User\View\UserCreateView
     *
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
    ) {
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier(
            $contentTypeIdentifier,
            $this->userLanguagePreferenceProvider->getPreferredLanguages()
        );
        $location = $this->locationService->loadLocation($parentLocationId);
        $language = $this->languageService->loadLanguage($language);
        $parentGroup = $this->userService->loadUserGroup($location->contentId);

        $data = (new UserCreateMapper())->mapToFormData($contentType, [$parentGroup], [
            'mainLanguageCode' => $language->languageCode,
        ]);
        $form = $this->createForm(UserCreateType::class, $data, [
            'languageCode' => $language->languageCode,
            'mainLanguageCode' => $language->languageCode,
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
     * Displays a user update form that updates user data and related content item.
     *
     * @param int $contentId ContentType id to create
     * @param int $versionNo Version number the version should be created from. Defaults to the currently published one.
     * @param string $language Language code to create the version in (eng-GB, ger-DE, ...))
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response|\Ibexa\ContentForms\User\View\UserUpdateView
     *
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
    ) {
        $user = $this->userService->loadUser($contentId);
        if (!$this->permissionResolver->canUser('content', 'edit', $user)) {
            throw new CoreUnauthorizedException('content', 'edit', ['userId' => $contentId]);
        }
        $contentType = $this->contentTypeService->loadContentType(
            $user->contentInfo->contentTypeId,
            $this->userLanguagePreferenceProvider->getPreferredLanguages()
        );

        $userUpdate = (new UserUpdateMapper())->mapToFormData($user, $contentType, [
            'languageCode' => $language,
        ]);

        try {
            // assume main location if no location was provided
            $location = $this->locationService->loadLocation(
                (int)$user->versionInfo->contentInfo->mainLocationId
            );
        } catch (UnauthorizedException $e) {
            // if no access to the main location assume content has multiple locations and first of them can be used
            $availableLocations = $this->locationService->loadLocations(
                $user->versionInfo->contentInfo
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
                'mainLanguageCode' => $user->contentInfo->mainLanguageCode,
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
        } catch (UnauthorizedException $e) {
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

class_alias(UserController::class, 'EzSystems\EzPlatformContentFormsBundle\Controller\UserController');
