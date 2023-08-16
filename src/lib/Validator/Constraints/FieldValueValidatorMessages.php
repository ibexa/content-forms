<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Validator\Constraints;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

/**
 * This class holds translation messages which comes from kernel as ValidationError messages.
 * It allows JMSTranslationBundle to extracting those messages.
 */
class FieldValueValidatorMessages implements TranslationContainerInterface
{
    /**
     * Returns an array of messages.
     *
     * @return \JMS\TranslationBundle\Model\Message[]
     */
    public static function getTranslationMessages(): array
    {
        return [
            new Message("User login '%login%' already in use. Enter a unique login.", 'validators'),
            new Message("Email '%email%' is used by another user. You must enter a unique email.", 'validators'),
            new Message('Invalid login format.', 'validators'),
        ];
    }
}

class_alias(FieldValueValidatorMessages::class, 'EzSystems\EzPlatformContentForms\Validator\Constraints\FieldValueValidatorMessages');
