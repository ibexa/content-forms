<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

use Ibexa\CodeStyle\PhpCsFixer\InternalConfigFactory;

return (new InternalConfigFactory())->buildConfig()->setFinder(
    PhpCsFixer\Finder::create()
        ->in([
            __DIR__ . '/src',
            __DIR__ . '/tests',
        ])
        ->files()->name('*.php')
    );
