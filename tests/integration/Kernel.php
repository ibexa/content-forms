<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\ContentForms;

use Ibexa\Bundle\ContentForms\IbexaContentFormsBundle;
use Ibexa\Contracts\Test\Core\IbexaTestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

final class Kernel extends IbexaTestKernel
{
    public function registerBundles(): iterable
    {
        yield from parent::registerBundles();

        yield new IbexaContentFormsBundle();
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        parent::registerContainerConfiguration($loader);

        $loader->load(__DIR__ . '/Resources/config.yaml');
    }
}
