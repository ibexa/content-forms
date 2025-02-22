<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\ContentForms\Controller;

use Ibexa\Bundle\Core\Controller;
use Ibexa\ContentForms\Content\View\ContentCreateDraftView;
use Ibexa\ContentForms\Content\View\ContentCreateSuccessView;
use Ibexa\ContentForms\Content\View\ContentCreateView;
use Ibexa\ContentForms\Content\View\ContentEditSuccessView;
use Ibexa\ContentForms\Content\View\ContentEditView;
use Ibexa\ContentForms\Data\Content\CreateContentDraftData;
use Ibexa\ContentForms\Form\ActionDispatcher\ActionDispatcherInterface;
use Ibexa\ContentForms\Form\Type\Content\ContentDraftCreateType;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Symfony\Component\HttpFoundation\Request;

class ContentEditController extends Controller
{
    public function __construct(
        private ContentTypeService $contentTypeService,
        private ContentService $contentService,
        private ActionDispatcherInterface $contentActionDispatcher
    ) {
    }

    /**
     * Displays and processes a content creation form. Showing the form does not create a draft in the repository.
     *
     * @param \Ibexa\ContentForms\Content\View\ContentCreateView $view
     *
     * @return \Ibexa\ContentForms\Content\View\ContentCreateView
     */
    public function createWithoutDraftAction(ContentCreateView $view): ContentCreateView
    {
        return $view;
    }

    /**
     * @param \Ibexa\ContentForms\Content\View\ContentCreateSuccessView $view
     *
     * @return \Ibexa\ContentForms\Content\View\ContentCreateSuccessView
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     */
    public function createWithoutDraftSuccessAction(ContentCreateSuccessView $view): ContentCreateSuccessView
    {
        return $view;
    }

    /**
     * Displays a draft creation form that creates a content draft from an existing content.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int|null $contentId
     * @param int $fromVersionNo
     * @param string $fromLanguage
     *
     * @return \Ibexa\ContentForms\Content\View\ContentCreateDraftView|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentType
     */
    public function createContentDraftAction(
        Request $request,
        ?int $contentId = null,
        ?int $fromVersionNo = null,
        ?string $fromLanguage = null
    ) {
        $createContentDraft = new CreateContentDraftData();
        $contentInfo = null;
        $contentType = null;

        if ($contentId !== null) {
            $createContentDraft->contentId = $contentId;

            $contentInfo = $this->contentService->loadContentInfo($contentId);
            $contentType = $this->contentTypeService->loadContentType($contentInfo->contentTypeId);
            $createContentDraft->fromVersionNo = $fromVersionNo ?: $contentInfo->currentVersionNo;
            $createContentDraft->fromLanguage = $fromLanguage ?: $contentInfo->mainLanguageCode;
        }

        $form = $this->createForm(
            ContentDraftCreateType::class,
            $createContentDraft,
            [
                'action' => $this->generateUrl('ibexa.content.draft.create'),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && null !== $form->getClickedButton()) {
            $this->contentActionDispatcher->dispatchFormAction($form, $createContentDraft, $form->getClickedButton()->getName());
            if ($response = $this->contentActionDispatcher->getResponse()) {
                return $response;
            }
        }

        return new ContentCreateDraftView(null, [
            'form' => $form->createView(),
            'contentInfo' => $contentInfo,
            'contentType' => $contentType,
        ]);
    }

    /**
     * @param \Ibexa\ContentForms\Content\View\ContentEditView $view
     *
     * @return \Ibexa\ContentForms\Content\View\ContentEditView
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     */
    public function editVersionDraftAction(ContentEditView $view): ContentEditView
    {
        return $view;
    }

    /**
     * @param \Ibexa\ContentForms\Content\View\ContentEditSuccessView $view
     *
     * @return \Ibexa\ContentForms\Content\View\ContentEditSuccessView
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     */
    public function editVersionDraftSuccessAction(ContentEditSuccessView $view): ContentEditSuccessView
    {
        return $view;
    }
}
