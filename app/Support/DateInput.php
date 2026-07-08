<?php

namespace App\Support;

final class DateInput
{
    public static function isYmd(?string $date): bool
    {
        if ($date === null || $date === '') {
            return false;
        }

        $parsed = \DateTime::createFromFormat('Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}
