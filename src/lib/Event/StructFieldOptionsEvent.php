<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Event;

use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\EventDispatcher\Event;

abstract class StructFieldOptionsEvent extends Event
{
    protected FormInterface $parentForm;

    protected FieldData $fieldData;

    /** @var array<string, mixed> */
    protected array $options;

    public function __construct(
        FormInterface $parentForm,
        FieldData $fieldData,
        array $options
    ) {
        $this->parentForm = $parentForm;
        $this->fieldData = $fieldData;
        $this->options = $options;
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

    /**
     * @param mixed $value
     */
    public function setOption(string $option, $value): void
    {
        $this->options[$option] = $value;
    }

    /**
     * @return mixed|null
     */
    public function getOption(string $option)
    {
        return $this->options[$option] ?? null;
    }
}
