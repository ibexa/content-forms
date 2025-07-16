<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Event;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

final class FormActionEvent extends FormEvent
{
    /**
     * Response to return after form post-processing. Typically, a RedirectResponse.
     */
    private ?Response $response = null;

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $payloads
     */
    public function __construct(
        FormInterface $form,
        mixed $data,
        private readonly string $clickedButton,
        private readonly array $options = [],
        private array $payloads = []
    ) {
        parent::__construct($form, $data);
    }

    public function getClickedButton(): string
    {
        return $this->clickedButton;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOption(string $optionName, mixed $defaultValue = null): mixed
    {
        if (!isset($this->options[$optionName])) {
            return $defaultValue;
        }

        return $this->options[$optionName];
    }

    public function hasOption(string $optionName): bool
    {
        return isset($this->options[$optionName]);
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    public function hasResponse(): bool
    {
        return $this->response !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayloads(): array
    {
        return $this->payloads;
    }

    /**
     * @param array<string, mixed> $payloads
     */
    public function setPayloads(array $payloads): void
    {
        $this->payloads = $payloads;
    }

    public function hasPayload(string $name): bool
    {
        return isset($this->payloads[$name]);
    }

    public function getPayload(string $name): mixed
    {
        return $this->payloads[$name];
    }

    public function setPayload(string $name, mixed $payload): void
    {
        $this->payloads[$name] = $payload;
    }
}
