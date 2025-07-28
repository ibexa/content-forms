<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\ConfigResolver;

use RuntimeException;

final class MaxUploadSize
{
    public const string BYTES = 'B';
    public const string KILOBYTES = 'K';
    public const string MEGABYTES = 'M';
    public const string GIGABYTES = 'G';

    private int $value;

    /**
     * Return value of upload_max_filesize in bytes.
     */
    public function get(?string $unit = null): int
    {
        if (!isset($this->value)) {
            $uploadMaxFilesize = ini_get('upload_max_filesize');
            if ($uploadMaxFilesize === false) {
                throw new RuntimeException('Could not retrieve upload_max_filesize from PHP configuration.');
            }

            $this->value = $this->stringToBytes($uploadMaxFilesize);
        }

        $value = $this->value;

        switch ($unit) {
            case self::GIGABYTES:
                $value /= 1024;
            case self::MEGABYTES:
                $value /= 1024;
            case self::KILOBYTES:
                $value /= 1024;
            case self::BYTES:
            default:
        }

        return $value;
    }

    private function stringToBytes(string $str): int
    {
        $str = strtoupper(trim($str));
        $value = (int)substr($str, 0, -1);
        $unit = substr($str, -1);

        switch ($unit) {
            case self::GIGABYTES:
                $value *= 1024;
            case self::MEGABYTES:
                $value *= 1024;
            case self::KILOBYTES:
                $value *= 1024;
        }

        return $value;
    }
}
