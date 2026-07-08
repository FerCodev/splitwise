<?php

namespace App\Models\Traits;

trait SanitizesNotificationErrorCode
{
    private function sanitizeErrorCode(string $code, string $fallback = 'unknown', ?int $maxLength = null): string
    {
        $code = trim($code);
        if (!in_array($code, self::ALLOWED_ERROR_CODES, true)) {
            $code = $fallback;
        }

        return $maxLength === null ? $code : mb_substr($code, 0, $maxLength);
    }
}
