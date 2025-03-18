<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Event;

use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentUpdateStruct;
use Symfony\Component\Form\FormInterface;

final class ContentUpdateFieldOptionsEvent extends StructFieldOptionsEvent
{
    private Content $content;

    private ContentUpdateStruct $contentUpdateStruct;

    public function __construct(
        Content $content,
        ContentUpdateStruct $contentUpdateStruct,
        FormInterface $parentForm,
        FieldData $fieldData,
        array $options
    ) {
        $this->content = $content;
        $this->contentUpdateStruct = $contentUpdateStruct;

        parent::__construct($parentForm, $fieldData, $options);
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getContentUpdateStruct(): ContentUpdateStruct
    {
        return $this->contentUpdateStruct;
    }
}
