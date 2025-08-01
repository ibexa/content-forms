<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Validator;

use Ibexa\Contracts\Core\Repository\Values\Translation\Plural;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @internal
 */
final class ValidationErrorsProcessor
{
    /** @var callable|null */
    private $propertyPathGenerator;

    /**
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     * @param callable|null $propertyPathGenerator
     */
    public function __construct(
        private readonly ExecutionContextInterface $context,
        callable $propertyPathGenerator = null
    ) {
        $this->propertyPathGenerator = $propertyPathGenerator;
    }

    /**
     * Builds constraint violations based on given SPI validation errors.
     *
     * @param \Ibexa\Contracts\Core\FieldType\ValidationError[] $validationErrors
     */
    public function processValidationErrors(array $validationErrors): void
    {
        if (empty($validationErrors)) {
            return;
        }

        $propertyPathGenerator = $this->propertyPathGenerator;
        foreach ($validationErrors as $i => $error) {
            $message = $error->getTranslatableMessage();
            $violationBuilder = $this->context->buildViolation($message instanceof Plural ? $message->plural : $message->message);
            $violationBuilder->setParameters($message->values);

            if ($propertyPathGenerator !== null) {
                $propertyPath = $propertyPathGenerator($i, $error->getTarget());
                if ($propertyPath) {
                    $violationBuilder->atPath($propertyPath);
                }
            }

            $violationBuilder->addViolation();
        }
    }
}
