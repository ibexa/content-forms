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
    /**
     * @param \Symfony\Component\Form\FormInterface<mixed> $parentForm
     * @param array<string, mixed> $options
     */
    public function __construct(
        protected FormInterface $parentForm,
        protected FieldData $fieldData,
        protected array $options
    ) {
    }

    /**
     * @return \Symfony\Component\Form\FormInterface<mixed>
     */
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

    public function getOption(string $option): mixed
    {
        return $this->options[$option] ?? null;
    }
}
