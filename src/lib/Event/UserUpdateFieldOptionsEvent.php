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
use Symfony\Contracts\EventDispatcher\Event;

final class UserUpdateFieldOptionsEvent extends Event
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content */
    private $content;

    /** @var \Ibexa\Contracts\Core\Repository\Values\User\UserUpdateStruct */
    private $userUpdateStruct;

    /** @var \Symfony\Component\Form\FormInterface */
    private $parentForm;

    /** @var \Ibexa\Contracts\ContentForms\Data\Content\FieldData */
    private $fieldData;

    /** @var array<string, mixed> */
    private $options;

    public function __construct(
        Content $content,
        UserUpdateStruct $userUpdateStruct,
        FormInterface $parentForm,
        FieldData $fieldData,
        array $options
    ) {
        $this->content = $content;
        $this->userUpdateStruct = $userUpdateStruct;
        $this->parentForm = $parentForm;
        $this->fieldData = $fieldData;
        $this->options = $options;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function setContent(Content $content): void
    {
        $this->content = $content;
    }

    public function getUserUpdateStruct(): UserUpdateStruct
    {
        return $this->userUpdateStruct;
    }

    public function getParentForm(): FormInterface
    {
        return $this->parentForm;
    }

    public function getFieldData(): FieldData
    {
        return $this->fieldData;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function setOption(string $option, $value): void
    {
        $this->options[$option] = $value;
    }

    public function getOption(string $option)
    {
        return $this->options[$option] ?? null;
    }
}
