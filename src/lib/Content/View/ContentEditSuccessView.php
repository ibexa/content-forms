<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Content\View;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Core\MVC\Symfony\View\BaseView;
use Ibexa\Core\MVC\Symfony\View\LocationValueView;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

class ContentEditSuccessView extends BaseView implements LocationValueView
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location|null */
    private $location;

    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentType
     */
    public function __construct(Response $response)
    {
        parent::__construct('@IbexaContentForms/http/302_empty_content.html.twig');

        $this->setResponse($response);
        $this->setControllerReference(new ControllerReference('ibexa_content_edit:editVersionDraftSuccessAction'));
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
}

class_alias(ContentEditSuccessView::class, 'EzSystems\EzPlatformContentForms\Content\View\ContentEditSuccessView');
