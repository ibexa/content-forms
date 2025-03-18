<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Content\View;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Core\MVC\Symfony\View\BaseView;
use Ibexa\Core\MVC\Symfony\View\ContentValueView;
use Ibexa\Core\MVC\Symfony\View\LocationValueView;
use Symfony\Component\Form\FormInterface;

class ContentEditView extends BaseView implements ContentValueView, LocationValueView
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content */
    private Content $content;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location|null */
    private ?Location $location = null;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Language */
    private Language $language;

    /** @var \Symfony\Component\Form\FormInterface */
    private FormInterface $form;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     */
    public function setContent(Content $content): void
    {
        $this->content = $content;
    }

    /**
     * Returns the Content.
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Content
     */
    public function getContent(): Content
    {
        return $this->content;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location|null $location
     */
    public function setLocation(?Location $location): void
    {
        $this->location = $location;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location|null
     */
    public function getLocation(): ?Location
    {
        return $this->location;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Language
     */
    public function getLanguage(): Language
    {
        return $this->language;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Language $language
     */
    public function setLanguage(Language $language): void
    {
        $this->language = $language;
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form
     */
    public function setForm(FormInterface $form): void
    {
        $this->form = $form;
    }
}
