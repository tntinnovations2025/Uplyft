<?php

namespace App\Services;

use App\Models\Institute;

class FeeCalculationService
{
    // Default system-wide financial fallbacks
    public const DEFAULT_BASE_FEE      = 10000.00; // Base admission fee
    public const FILER_TAX_RATE        = 0.05;     // 5% tax rate for Filers
    public const NON_FILER_TAX_RATE    = 0.15;     // 15% tax rate for Non-Filers

    /**
     * Compute the full fee breakdown for a student admission.
     *
     * @param string $taxStatus ('filer' or 'non-filer')
     * @param int|null $instituteId Target tenant ID to pull custom pricing settings from.
     * @return array{
     *     base_fee: float,
     *     tax_rate: float,
     *     tax_amount: float,
     *     total_fee: float
     * }
     */
    public function calculate(string $taxStatus, ?int $instituteId = null): array
    {
        $baseFee = self::DEFAULT_BASE_FEE;
        $filerRate = self::FILER_TAX_RATE;
        $nonFilerRate = self::NON_FILER_TAX_RATE;

        // If a tenant context is available, try to resolve institute-level custom parameters
        if ($instituteId) {
            $institute = Institute::find($instituteId);
            if ($institute && isset($institute->settings)) {
                $baseFee = (float) ($institute->settings['base_admission_fee'] ?? self::DEFAULT_BASE_FEE);
                $filerRate = (float) ($institute->settings['filer_tax_rate'] ?? self::FILER_TAX_RATE);
                $nonFilerRate = (float) ($institute->settings['non_filer_tax_rate'] ?? self::NON_FILER_TAX_RATE);
            }
        }

        // Apply tax rate logic based on guardian filer status
        $taxRate = ($taxStatus === 'filer') ? $filerRate : $nonFilerRate;

        // Perform financial math with rounding safeguards
        $taxAmount = round($baseFee * $taxRate, 2);
        $totalFee  = round($baseFee + $taxAmount, 2);

        return [
            'base_fee'   => $baseFee,
            'tax_rate'   => $taxRate,
            'tax_amount' => $taxAmount,
            'total_fee'  => $totalFee,
        ];
    }
}
