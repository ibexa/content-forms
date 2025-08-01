<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Event;

use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\Values\User\UserCreateStruct;
use Symfony\Component\Form\FormInterface;

final class UserCreateFieldOptionsEvent extends StructFieldOptionsEvent
{
    public function __construct(
        private readonly UserCreateStruct $userCreateStruct,
        FormInterface $parentForm,
        FieldData $fieldData,
        array $options
    ) {
        parent::__construct($parentForm, $fieldData, $options);
    }

    public function getUserCreateStruct(): UserCreateStruct
    {
        return $this->userCreateStruct;
    }
}
