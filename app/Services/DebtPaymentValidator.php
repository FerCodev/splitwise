<?php

namespace App\Services;

final class DebtPaymentValidator
{
    public static function amountToCents(mixed $value): ?int
    {
        if (!is_scalar($value)) {
            return null;
        }

        $normalized = str_replace(',', '.', trim((string) $value));
        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $normalized)) {
            return null;
        }

        [$units, $decimals] = array_pad(explode('.', $normalized, 2), 2, '');
        $cents = ((int) $units * 100) + (int) str_pad($decimals, 2, '0');

        return $cents > 0 ? $cents : null;
    }

    public static function validateAgainstDebt(mixed $amount, mixed $debt): ?string
    {
        $amountCents = self::amountToCents($amount);
        if ($amountCents === null) {
            return 'El monto debe ser mayor a cero y tener como máximo dos decimales.';
        }

        $debtCents = self::amountToCents($debt);
        if ($debtCents === null) {
            return 'La deuda vigente no tiene un monto válido.';
        }

        if ($amountCents > $debtCents) {
            return 'El monto no puede superar la deuda pendiente.';
        }

        return null;
    }

    public static function validateCurrentDebt(?array $debt, mixed $amount): ?string
    {
        if ($debt === null) {
            return 'Esta deuda ya fue saldada o cambió. Actualizá la página.';
        }

        return self::validateAgainstDebt($amount, $debt['monto'] ?? null);
    }

    public static function centsToDecimal(int $cents): float
    {
        return $cents / 100;
    }
}
