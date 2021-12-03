<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Password extends Constraint
{
    /** @var string */
    public $message = 'ez.user.password.invalid';

    /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType|null */
    public $contentType;

    /**
     * {@inheritdoc}
     */
    public function getTargets(): array
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}

class_alias(Password::class, 'EzSystems\EzPlatformContentForms\Validator\Constraints\Password');
