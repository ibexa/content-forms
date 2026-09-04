<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\MinkExtension\Context\MinkContext;
use Ibexa\Bundle\Core\Features\Context\YamlConfigurationContext;
use Ibexa\ContentForms\Behat\Context\ContentEditContext;
use Ibexa\ContentForms\Behat\Context\ContentTypeContext;
use Ibexa\ContentForms\Behat\Context\FieldTypeFormContext;
use Ibexa\ContentForms\Behat\Context\PagelayoutContext;
use Ibexa\ContentForms\Behat\Context\SelectionFieldTypeFormContext;
use Ibexa\ContentForms\Behat\Context\UserRegistrationContext;

return (new Config())
    ->withProfile((new Profile('setup-content-forms'))
        ->withSuite((new Suite('setup'))
            ->withContexts(
                UserRegistrationContext::class,
                YamlConfigurationContext::class
            )
            ->withPaths('vendor/ibexa/content-forms/features/User/Setup')))
    ->withProfile((new Profile('content-forms'))
        ->withSuite((new Suite('content_edit'))
            ->withContexts(
                ContentEditContext::class,
                ContentTypeContext::class,
                PagelayoutContext::class
            )
            ->withPaths('vendor/ibexa/content-forms/features/ContentEdit'))
        ->withSuite((new Suite('fieldtype_form'))
            ->withContexts(
                ContentTypeContext::class,
                FieldTypeFormContext::class,
                SelectionFieldTypeFormContext::class
            )
            ->withPaths('vendor/ibexa/content-forms/features/FieldTypeForm'))
        ->withSuite((new Suite('user_registration'))
            ->withContexts(
                UserRegistrationContext::class,
                MinkContext::class,
                YamlConfigurationContext::class
            )
            ->withPaths('vendor/ibexa/content-forms/features/User/Registration')));
