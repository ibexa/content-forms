<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Form\Type\FieldType;

use Ibexa\Contracts\Core\Limitation\Target;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Core\Repository\Values\User\LookupLimitationResult;
use Symfony\Component\Form\AbstractType;

abstract class AbstractRelationFieldType extends AbstractType
{
    private ContentService $contentService;

    private PermissionResolver $permissionResolver;

    public function __construct(
        ContentService $contentService,
        PermissionResolver $permissionResolver
    ) {
        $this->permissionResolver = $permissionResolver;
        $this->contentService = $contentService;
    }

    /**
     * @return array{
     *     contentInfo: \Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|null,
     *     contentType: \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType|null,
     *     contentId: int,
     *     languageCodes: array<string>,
     *     unauthorized: bool,
     * }
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    protected function getRelationData(int $contentId): array
    {
        $contentInfo = null;
        $contentType = null;
        $languageCodes = [];
        $unauthorized = false;

        try {
            $versionInfo = $this->contentService->loadVersionInfoById($contentId);
            $contentInfo = $versionInfo->getContentInfo();
            $contentType = $contentInfo->getContentType();
            $languageCodes = $this->getAvailableLanguageCodes(
                $contentInfo,
                $this->extractLanguageCodes($versionInfo->getLanguages())
            );
        } catch (UnauthorizedException $e) {
            $unauthorized = true;
        }

        return [
            'contentInfo' => $contentInfo,
            'contentType' => $contentType,
            'contentId' => $contentId,
            'languageCodes' => $languageCodes,
            'unauthorized' => $unauthorized,
        ];
    }

    /**
     * @param array<string> $languageCodes
     *
     * @return array<string>
     */
    protected function getAvailableLanguageCodes(ContentInfo $contentInfo, array $languageCodes): array
    {
        $lookupLimitationResult = $this->getLookupLimitationResult($contentInfo, $languageCodes);
        if (
            empty($lookupLimitationResult->lookupPolicyLimitations)
            && empty($lookupLimitationResult->roleLimitations)
        ) {
            return $languageCodes;
        }

        $limitationLanguageCodes = $this->getLimitationLanguageCodes($lookupLimitationResult);

        return array_filter(
            $languageCodes,
            static function (string $languageCode) use ($limitationLanguageCodes): bool {
                return in_array($languageCode, $limitationLanguageCodes, true);
            }
        );
    }

    private function getLookupLimitationResult(ContentInfo $contentInfo, array $languageCodes): LookupLimitationResult
    {
        return $this->permissionResolver->lookupLimitations(
            'content',
            'edit',
            $contentInfo,
            [
                (new Target\Builder\VersionBuilder())->translateToAnyLanguageOf($languageCodes)->build(),
                $contentInfo->getMainLocation(),
            ],
            [Limitation::LANGUAGE]
        );
    }

    /**
     * @return array<string>
     */
    private function getLimitationLanguageCodes(LookupLimitationResult $lookupLimitations): array
    {
        $limitationLanguageCodes = [];
        foreach ($lookupLimitations->roleLimitations as $roleLimitation) {
            foreach ($roleLimitation->limitationValues as $limitationValue) {
                $limitationLanguageCodes[$limitationValue] = true;
            }
        }

        foreach ($lookupLimitations->lookupPolicyLimitations as $lookupPolicyLimitation) {
            foreach ($lookupPolicyLimitation->limitations as $limitation) {
                foreach ($limitation->limitationValues as $limitationValue) {
                    $limitationLanguageCodes[$limitationValue] = true;
                }
            }
        }

        return array_keys($limitationLanguageCodes);
    }

    /**
     * @param array<\Ibexa\Contracts\Core\Repository\Values\Content\Language> $languages
     *
     * @return array<string>
     */
    private function extractLanguageCodes(array $languages): array
    {
        $languageCodes = [];
        foreach ($languages as $language) {
            $languageCodes[] = $language->getLanguageCode();
        }

        return $languageCodes;
    }
}
