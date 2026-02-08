<?php
class RepaymentCalculator {
    /**
     * Calculate loan repayments (amortization)
     */
    public static function calculateLoanRepayments(
        float $principal,
        float $annualInterestRate,
        int $termMonths,
        DateTime $startDate,
        string $frequency = 'monthly'
    ): array {
        // Implementation of amortization calculation
    }

    /**
     * Calculate rental payments
     */
    public static function calculateRentalPayments(
        float $rate,
        string $period,
        DateTime $startDate,
        DateTime $endDate
    ): array {
        // Implementation of rental payment schedule
    }
}