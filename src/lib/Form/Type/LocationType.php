<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

final class LocationType extends AbstractType
{
    public function __construct(private readonly LocationService $locationService)
    {
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['destination_location'] = null;

        if ($view->vars['value']) {
            try {
                $view->vars['destination_location'] = $this->locationService->loadLocation(
                    (int)$view->vars['value']
                );
            } catch (NotFoundException | UnauthorizedException) {
                //do nothing
            }
        }
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }
}
