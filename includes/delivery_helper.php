<?php

class DeliveryHelper {
    /**
     * Calculate distance between two points using Haversine formula
     * @return float Distance in KM
     */
    public static function calculateHaversineDistance($lat1, $lon1, $lat2, $lon2) {
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) return 0;

        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return round($distance, 2);
    }

    /**
     * Calculate delivery earnings based on distance
     * Hybrid Model: Base Pay (₹35) vs Calculated Pay (Distance * 1.3 * 5)
     */
    public static function calculateEarnings($distanceKm) {
        $minPay = 25; // Minimum guarantee per order
        
        // Approximate road distance factor: 1.3
        $roadDistance = $distanceKm * 1.3;
        // Rate per KM: 5
        $calculatedEarning = $roadDistance * 5;
        
        // If calculated is less than minimum, give minimum
        return round(max($minPay, $calculatedEarning), 2);
    }
}
?>
