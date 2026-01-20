<?php
/**
 * Funcții de calcul - windchill, predictibilitate, conversii
 */

if (!function_exists('calculeazaWindchill')) {
    /**
     * Calculează windchill (temperatura resimțită)
     * Formula: Environment Canada
     */
    function calculeazaWindchill($temperatura, $vant_kmh) {
        // Dacă vânt < 5 km/h, windchill = temperatura
        if ($vant_kmh < 5) {
            return $temperatura;
        }
        
        // Formula windchill
        $windchill = 13.12 + 0.6215 * $temperatura 
                    - 11.37 * pow($vant_kmh, 0.16) 
                    + 0.3965 * $temperatura * pow($vant_kmh, 0.16);
        
        return round($windchill, 1);
    }
}

if (!function_exists('calculeazaPredictibilitate')) {
    /**
     * Calculează predictibilitate prognoză în funcție de numărul de zile
     * 1 zi = 95%, 10 zile = 65%
     */
    function calculeazaPredictibilitate($numar_zile) {
        if ($numar_zile <= 0) {
            return 100;
        }
        
        // Scădere liniară: 95% la 1 zi, 65% la 10 zile
        $predictibilitate = 95 - ($numar_zile - 1) * 3.33;
        
        return max(65, min(95, round($predictibilitate)));
    }
}

if (!function_exists('msToKmh')) {
    /**
     * Convertește m/s în km/h
     */
    function msToKmh($ms) {
        return round($ms * 3.6, 1);
    }
}

if (!function_exists('kmhToMs')) {
    /**
     * Convertește km/h în m/s
     */
    function kmhToMs($kmh) {
        return round($kmh / 3.6, 1);
    }
}

