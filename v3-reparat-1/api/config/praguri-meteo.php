<?php
/**
 * Praguri pentru evaluarea factorilor meteo
 * Folosite pentru a determina status: GO / CAUTION / NO-GO
 */

return [
    'windchill' => [
        'GO' => ['min' => -20, 'max' => 100],           // > -20°C
        'CAUTION' => ['min' => -30, 'max' => -20],      // -30°C ... -20°C
        'NO-GO' => ['min' => -100, 'max' => -30]        // < -30°C
    ],
    
    'vant' => [
        'GO' => ['min' => 0, 'max' => 40],              // 0-40 km/h
        'CAUTION' => ['min' => 40, 'max' => 60],        // 40-60 km/h
        'NO-GO' => ['min' => 60, 'max' => 200]          // > 60 km/h
    ],
    
    'zapada' => [
        'GO' => ['min' => 0, 'max' => 50],              // 0-50 cm
        'CAUTION' => ['min' => 50, 'max' => 150],       // 50-150 cm
        'NO-GO' => ['min' => 150, 'max' => 500]         // > 150 cm
    ],
    
    'temperatura' => [
        'GO' => ['min' => -15, 'max' => 40],            // > -15°C
        'CAUTION' => ['min' => -25, 'max' => -15],      // -25°C ... -15°C
        'NO-GO' => ['min' => -50, 'max' => -25]         // < -25°C
    ],
    
    // Factori critici care forțează ROȘU automat
    'factori_critici' => [
        'avalansa_risc' => 4,           // Risc avalanșă >= 4 → ROȘU
        'vant_extrem' => 80,            // Vânt >= 80 km/h → ROȘU
        'windchill_extrem' => -35,      // Windchill <= -35°C → ROȘU
        'viscol' => true                // Viscol activ → ROȘU
    ],
    
    // Praguri pentru agregare status meteo
    'agregare' => [
        'no_go_min' => 1,               // Minim 1 factor NO-GO → Meteo ROȘU
        'caution_min' => 3              // Minim 3 factori CAUTION → Meteo GALBEN
    ]
];
