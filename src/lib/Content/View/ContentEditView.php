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
    private Content $content;

    private ?Location $location = null;

    private Language $language;

    /** @var \Symfony\Component\Form\FormInterface<mixed> */
    private FormInterface $form;

    public function setContent(Content $content): void
    {
        $this->content = $content;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function setLocation(?Location $location): void
    {
        $this->location = $location;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function setLanguage(Language $language): void
    {
        $this->language = $language;
    }

    /**
     * @return \Symfony\Component\Form\FormInterface<mixed>
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    /**
     * @param \Symfony\Component\Form\FormInterface<mixed> $form
     */
    public function setForm(FormInterface $form): void
    {
        $this->form = $form;
    }
}
