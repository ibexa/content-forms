<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\ActionDispatcher;

use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Form action dispatchers can be used to abstract actions when a complex form is submitted.
 * A typical example is a multiple actions-based form, with multiple submit buttons, where actions to take depend on
 * which submit button is clicked.
 *
 * This would basically help reducing the amount of code in the controller receiving the form submission request.
 */
interface ActionDispatcherInterface
{
    /**
     * Dispatches the action of a given form.
     *
     * @param \Symfony\Component\Form\FormInterface<mixed> $form the form that has been submitted
     * @param \Ibexa\Contracts\Core\Repository\Values\ValueObject $data Underlying data for the form. Most likely a create or update struct.
     * @param string|null $actionName The form action itself. Typically, the form clicked button name, or null if the default action is used (e.g., when pressing enter).
     * @param array<string, mixed> $options arbitrary hash of options
     */
    public function dispatchFormAction(
        FormInterface $form,
        ValueObject $data,
        ?string $actionName = null,
        array $options = []
    ): void;

    public function getResponse(): ?Response;
}
