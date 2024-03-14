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
use Symfony\Contracts\EventDispatcher\Event;

final class UserCreateFieldOptionsEvent extends Event
{
    /** @var \Ibexa\Contracts\Core\Repository\Values\User\UserCreateStruct */
    private $userCreateStruct;

    /** @var \Symfony\Component\Form\FormInterface */
    private $parentForm;

    /** @var \Ibexa\Contracts\ContentForms\Data\Content\FieldData */
    private $fieldData;

    /** @var array<string, mixed> */
    private $options;

    public function __construct(
        UserCreateStruct $userCreateStruct,
        FormInterface $parentForm,
        FieldData $fieldData,
        array $options
    ) {
        $this->userCreateStruct = $userCreateStruct;
        $this->parentForm = $parentForm;
        $this->fieldData = $fieldData;
        $this->options = $options;
    }

    public function getUserCreateStruct(): UserCreateStruct
    {
        return $this->userCreateStruct;
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
