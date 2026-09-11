<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Validator\Constraints;

use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Password extends Constraint
{
    protected const string MESSAGE = 'ez.user.password.invalid';

    public string $message = self::MESSAGE;

    public ?ContentType $contentType;

    /**
     * @param array<string>|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        ?ContentType $contentType = null,
        ?string $message = null,
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct(null, $groups, $payload);

        $this->contentType = $contentType;
        $this->message = $message ?? static::MESSAGE;
    }

    public function getTargets(): array
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
