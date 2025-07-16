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
    protected const int SELECTION_SELF = -1;

    public function __construct(
        protected readonly ContentTypeService $contentTypeService,
        protected readonly LocationService $locationService
    ) {
    }

    /**
     * @return array<string, string>
     */
    protected function getContentTypesHash(): array
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
     */
    protected function loadDefaultLocationForSelection(
        ?int $defaultLocationId = null,
        ?Location $currentLocation = null
    ): ?Location {
        if (empty($defaultLocationId)) {
            return null;
        }

        try {
            if ($defaultLocationId === self::SELECTION_SELF) {
                return $currentLocation;
            }

            return $this->locationService->loadLocation($defaultLocationId);
        } catch (NotFoundException | UnauthorizedException) {
            return null;
        }
    }
}
