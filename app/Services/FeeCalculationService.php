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
     * @param float|null $customBaseFee Optional custom base fee override.
     * @param float|null $customTaxRate Optional custom tax percentage override (e.g. 5 for 5%).
     * @param float|null $scholarshipPercentage Optional scholarship percentage discount (e.g. 20 for 20%).
     * @return array{
     *     base_fee: float,
     *     scholarship_percentage: float,
     *     scholarship_amount: float,
     *     subtotal_after_scholarship: float,
     *     tax_rate: float,
     *     tax_percentage: float,
     *     tax_amount: float,
     *     grand_total: float
     * }
     */
    public function calculate(
        string $taxStatus,
        ?int $instituteId = null,
        ?float $customBaseFee = null,
        ?float $customTaxRate = null,
        ?float $scholarshipPercentage = 0.0,
        ?float $admissionFee = 0.0,
        ?float $securityFee = 0.0
    ): array {
        $baseFee = $customBaseFee ?? self::DEFAULT_BASE_FEE;
        $filerRate = self::FILER_TAX_RATE;
        $nonFilerRate = self::NON_FILER_TAX_RATE;

        $admFee = (float) ($admissionFee ?? 0.0);
        $secFee = (float) ($securityFee ?? 0.0);

        // If a tenant context is available, try to resolve institute-level custom parameters
        if ($instituteId) {
            $institute = Institute::find($instituteId);
            if ($institute && isset($institute->settings)) {
                $baseFee = $customBaseFee ?? (float) ($institute->settings['base_admission_fee'] ?? self::DEFAULT_BASE_FEE);
                $filerRate = (float) ($institute->settings['filer_tax_rate'] ?? self::FILER_TAX_RATE);
                $nonFilerRate = (float) ($institute->settings['non_filer_tax_rate'] ?? self::NON_FILER_TAX_RATE);
            }
        }

        // Determine Tax Rate Decimal (e.g., 0.05 for 5%)
        if ($customTaxRate !== null && $customTaxRate !== '' && is_numeric($customTaxRate)) {
            $taxRateDecimal = (float) $customTaxRate / 100.0;
        } else {
            $taxRateDecimal = ($taxStatus === 'filer') ? $filerRate : $nonFilerRate;
        }
        $taxPercentage = $taxRateDecimal * 100.0;

        // Determine Scholarship Discount Amount (applied to base tuition)
        $scholarshipPct = max(0.0, min(100.0, (float) ($scholarshipPercentage ?? 0.0)));
        $scholarshipAmount = round($baseFee * ($scholarshipPct / 100.0), 2);
        $subtotalAfterScholarship = max(0.0, round($baseFee - $scholarshipAmount, 2));

        // Tax calculation on (post-scholarship tuition + admission fee)
        $taxableAmount = $subtotalAfterScholarship + $admFee;
        $taxAmount = round($taxableAmount * $taxRateDecimal, 2);
        $grandTotal = round($taxableAmount + $taxAmount + $secFee, 2);

        return [
            'base_fee' => $baseFee,
            'admission_fee' => $admFee,
            'security_fee' => $secFee,
            'scholarship_percentage' => $scholarshipPct,
            'scholarship_amount' => $scholarshipAmount,
            'subtotal_after_scholarship' => $subtotalAfterScholarship,
            'tax_rate' => $taxRateDecimal,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
        ];
    }
}
