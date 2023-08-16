<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\FieldType\Mapper;

use Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;

abstract class AbstractRelationFormMapper implements FieldValueFormMapperInterface
{
    protected const SELECTION_SELF = -1;

    /**
     * @var \Ibexa\Contracts\Core\Repository\ContentTypeService Used to fetch list of available content types
     */
    protected $contentTypeService;

    /**
     * @var \Ibexa\Contracts\Core\Repository\LocationService Used to fetch selection root
     */
    protected $locationService;

    /**
     * @param \Ibexa\Contracts\Core\Repository\ContentTypeService $contentTypeService
     * @param \Ibexa\Contracts\Core\Repository\LocationService $locationService
     */
    public function __construct(ContentTypeService $contentTypeService, LocationService $locationService)
    {
        $this->contentTypeService = $contentTypeService;
        $this->locationService = $locationService;
    }

    /**
     * Fill a hash with all content types and their ids.
     *
     * @return array
     */
    protected function getContentTypesHash()
    {
        $contentTypeHash = [];
        foreach ($this->contentTypeService->loadContentTypeGroups() as $contentTypeGroup) {
            foreach ($this->contentTypeService->loadContentTypes($contentTypeGroup) as $contentType) {
                $contentTypeHash[$contentType->getName()] = $contentType->identifier;
            }
        }
        ksort($contentTypeHash);

        return $contentTypeHash;
    }

    /**
     * Loads location which is starting point for selecting destination content object.
     *
     * @param null $defaultLocationId
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Location|null
     */
    protected function loadDefaultLocationForSelection($defaultLocationId = null, ?Location $currentLocation = null): ?Location
    {
        if (!empty($defaultLocationId)) {
            try {
                if ($defaultLocationId === self::SELECTION_SELF) {
                    return $currentLocation;
                }

                return $this->locationService->loadLocation((int)$defaultLocationId);
            } catch (NotFoundException | UnauthorizedException $e) {
            }
        }

        return null;
    }
}

class_alias(AbstractRelationFormMapper::class, 'EzSystems\EzPlatformContentForms\FieldType\Mapper\AbstractRelationFormMapper');
