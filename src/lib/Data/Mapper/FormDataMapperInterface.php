<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Data\Mapper;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;

/**
 * A FormDataMapper will convert a value object from Ibexa content repository to a usable form data.
 */
interface FormDataMapperInterface
{
    /**
     * Maps a ValueObject from Ibexa content repository to a data usable as underlying form data (e.g. create/update struct).
     *
     * @param array<string, mixed> $params
     */
    public function mapToFormData(ValueObject $repositoryValueObject, array $params = []): mixed;
}
