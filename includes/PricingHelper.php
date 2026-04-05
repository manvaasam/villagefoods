<?php
/**
 * PricingHelper.php
 */
require_once 'settings_helper.php';

class PricingHelper {
    /**
     * Calculates the full bill breakdown, payouts, and platform profit.
     */
    public static function calculateBill(float $productTotal, float $distance) {
        $platformFee = (float)Settings::get('platform_fee', 10.00);
        $handlingFee = (float)Settings::get('handling_fee', 10.00);
        $commissionPercent = (float)Settings::get('vendor_commission_percentage', 20.00);
        
        $deliveryCharge = self::calculateDeliveryCharge($productTotal, $distance);
        $dpp = self::calculateDeliveryPartnerPayout($distance);
        
        // Commission Logic
        $commissionAmount = ($productTotal * $commissionPercent) / 100;
        $vendorEarning = $productTotal - $commissionAmount;
        
        $totalPayable = $productTotal + $deliveryCharge + $platformFee + $handlingFee;
        
        // Metadata for UI
        $deliveryType = "flat_rate";
        $message = "Standard Delivery Fee Applied";

        // Platform Profit = (Fees + Customer Delivery Charge + Vendor Commission) - Partner Payout
        $platformProfit = ($platformFee + $handlingFee + $deliveryCharge + $commissionAmount) - $dpp;
        
        return [
            'product_total' => round($productTotal, 2),
            'delivery_charge' => round($deliveryCharge, 2),
            'platform_fee' => round($platformFee, 2),
            'handling_fee' => round($handlingFee, 2),
            'total_payable' => round($totalPayable, 2),
            'vendor_earning' => round($vendorEarning, 2),
            'commission_rate' => round($commissionPercent, 2),
            'commission_amount' => round($commissionAmount, 2),
            'delivery_partner_payout' => round($dpp, 2),
            'platform_profit' => round($platformProfit, 2),
            'distance' => round($distance, 2),
            'delivery_metadata' => [
                'type' => $deliveryType,
                'message' => $message,
                'remaining_for_offer' => 0
            ]
        ];
    }

    /**
     * Internal logic for Customer Delivery Charge
     */
    private static function calculateDeliveryCharge(float $productTotal, float $distance) {
        // Return dynamic fee from settings (fallback to 40)
        return (float)Settings::get('base_delivery_fee', 40.00);
    }

    /**
     * Internal logic for Delivery Partner Payout
     */
    private static function calculateDeliveryPartnerPayout(float $distance) {
        // Standardize partner payout as well (fallback to 40)
        return (float)Settings::get('base_delivery_fee', 40.00);
    }
}
?>
