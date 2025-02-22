<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

final class LocationChoiceType extends AbstractType
{
    #[Override]
    public function getParent(): string
    {
        return ChoiceType::class;
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'ibexa_form_type_location_choice';
    }
}
