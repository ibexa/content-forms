<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Event;

use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\User\UserUpdateStruct;
use Symfony\Component\Form\FormInterface;

final class UserUpdateFieldOptionsEvent extends StructFieldOptionsEvent
{
    public function __construct(
        private readonly Content $content,
        private readonly UserUpdateStruct $userUpdateStruct,
        FormInterface $parentForm,
        FieldData $fieldData,
        array $options
    ) {
        parent::__construct($parentForm, $fieldData, $options);
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getUserUpdateStruct(): UserUpdateStruct
    {
        return $this->userUpdateStruct;
    }
}
