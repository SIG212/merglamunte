<?php
/**
 * Mapping masive -> stații meteo ANM + surse Meteoblue
 * 
 * Stațiile sunt folosite pentru:
 * - Vizibilitate actuală (ANM)
 * - Grosime zăpadă actuală (ANM)
 * - Temperatură actuală (ANM)
 * - Prognoză (Meteoblue)
 */

return [
    // ═══════════════════════════════════════════════════════════════
    // ⭐ CELE MAI CĂUTATE
    // ═══════════════════════════════════════════════════════════════
    
    'bucegi' => [
        'anm_peste_prag' => 'VARFUL OMU',
        'anm_sub_prag' => 'SINAIA 1500',
        'prag_altitudine' => 1800,
        'meteoblue_peste_prag' => 'bucegi-mountains_romania_683598',
        'meteoblue_sub_prag' => ['lat' => 45.467, 'lon' => 25.500],
        'altitudine_maxima' => 2505
    ],
    
    'fagaras' => [
        'anm_peste_prag' => 'BALEA LAC',
        'anm_sub_prag' => 'VOINEASA',
        'prag_altitudine' => 1800,
        'meteoblue_peste_prag' => 'făgăraș-mountains_romania_678498',
        'meteoblue_sub_prag' => ['lat' => 45.653, 'lon' => 24.787],
        'altitudine_maxima' => 2544
    ],
    
    'retezat' => [
        'anm_peste_prag' => 'PARANG',
        'anm_sub_prag' => 'STRAJA',
        'prag_altitudine' => 1800,
        'meteoblue_peste_prag' => 'vf.-retezat_romania_11810090',
        'meteoblue_sub_prag' => ['lat' => 45.372, 'lon' => 23.064],
        'altitudine_maxima' => 2509
    ],
    
    'piatra-craiului' => [
        'anm_peste_prag' => 'VARFUL OMU',
        'anm_sub_prag' => 'PREDEAL',
        'prag_altitudine' => 1800,
        'meteoblue_peste_prag' => 'piatra-craiului-mountains_romania_670901',
        'meteoblue_sub_prag' => ['lat' => 45.506, 'lon' => 25.265],
        'altitudine_maxima' => 2238
    ],
    
    // ═══════════════════════════════════════════════════════════════
    // 🏔️ CARPAȚII MERIDIONALI
    // ═══════════════════════════════════════════════════════════════
    
    'baiului' => [
        'anm_peste_prag' => 'SINAIA 1500',
        'anm_sub_prag' => 'PREDEAL',
        'prag_altitudine' => 1500,
        'meteoblue_peste_prag' => 'baiu-mountains_romania_685760',
        'meteoblue_sub_prag' => ['lat' => 45.412, 'lon' => 25.667],
        'altitudine_maxima' => 1923
    ],
    
    'buila' => [
        'anm_peste_prag' => 'VOINEASA',
        'anm_sub_prag' => 'POLOVRAGI',
        'prag_altitudine' => 1500,
        'meteoblue_peste_prag' => 'buila_romania_8410689',
        'meteoblue_sub_prag' => ['lat' => 45.238, 'lon' => 24.132],
        'altitudine_maxima' => 1885
    ],
    
    'cindrel' => [
        'anm_peste_prag' => 'PALTINIS',
        'anm_sub_prag' => 'PALTINIS',
        'prag_altitudine' => 1400,
        'meteoblue_peste_prag' => 'munţii-cindrel_romania_681803',
        'meteoblue_sub_prag' => ['lat' => 45.623, 'lon' => 23.892],
        'altitudine_maxima' => 2244
    ],
    
    'ciucas' => [
        'anm_peste_prag' => 'SINAIA 1500',
        'anm_sub_prag' => 'PREDEAL',
        'prag_altitudine' => 1500,
        'meteoblue_peste_prag' => ['lat' => 45.522, 'lon' => 25.926],
        'meteoblue_sub_prag' => 'ciucas_romania_7874266',
        'altitudine_maxima' => 1954
    ],
    
    'cozia' => [
        'anm_peste_prag' => 'VOINEASA',
        'anm_sub_prag' => 'POLOVRAGI',
        'prag_altitudine' => 1400,
        'meteoblue_peste_prag' => 'cozia_romania_8260398',
        'meteoblue_sub_prag' => ['lat' => 45.302, 'lon' => 24.341],
        'altitudine_maxima' => 1668
    ],
    
    'godeanu' => [
        'anm_peste_prag' => 'TARCU',
        'anm_sub_prag' => 'CUNTU',
        'prag_altitudine' => 1800,
        'meteoblue_peste_prag' => 'muntele-Ţarcul_romania_665512',
        'meteoblue_sub_prag' => 'Țarcu-mountains_romania_665513',
        'altitudine_maxima' => 2291
    ],
    
    'iezer' => [
        'anm_peste_prag' => 'VARFUL OMU',
        'anm_sub_prag' => 'SINAIA 1500',
        'prag_altitudine' => 1800,
        'meteoblue_peste_prag' => 'munţii-iezer_romania_675717',
        'meteoblue_sub_prag' => ['lat' => 45.464, 'lon' => 25.088],
        'altitudine_maxima' => 2462
    ],
    
    'parang' => [
        'anm_peste_prag' => 'PARANG',
        'anm_sub_prag' => 'STRAJA',
        'prag_altitudine' => 1800,
        'meteoblue_peste_prag' => 'parâng-mountains_romania_671333',
        'meteoblue_sub_prag' => ['lat' => 45.387, 'lon' => 23.482],
        'altitudine_maxima' => 2519
    ],
    
    'tarcu' => [
        'anm_peste_prag' => 'TARCU',
        'anm_sub_prag' => 'CUNTU',
        'prag_altitudine' => 1800,
        'meteoblue_peste_prag' => 'muntele-Ţarcul_romania_665512',
        'meteoblue_sub_prag' => 'Țarcu-mountains_romania_665513',
        'altitudine_maxima' => 2190
    ],
    
    // ═══════════════════════════════════════════════════════════════
    // 🌲 CARPAȚII ORIENTALI
    // ═══════════════════════════════════════════════════════════════
    
    'bistritei' => [
        'anm_peste_prag' => 'CALIMANI (RETITIS)',
        'anm_sub_prag' => 'POIANA STAMPEI',
        'prag_altitudine' => 1600,
        'meteoblue_peste_prag' => 'giumalău_romania_677132',
        'meteoblue_sub_prag' => ['lat' => 47.111, 'lon' => 25.247],
        'altitudine_maxima' => 1859
    ],
    
    'calimani' => [
        'anm_peste_prag' => 'CALIMANI (RETITIS)',
        'anm_sub_prag' => 'POIANA STAMPEI',
        'prag_altitudine' => 1800,
        'meteoblue_peste_prag' => 'pietrosul-călimanilor_romania_670813',
        'meteoblue_sub_prag' => ['lat' => 47.111, 'lon' => 25.247],
        'altitudine_maxima' => 2100
    ],
    
    'ceahlau' => [
        'anm_peste_prag' => 'CEAHLAU TOACA',
        'anm_sub_prag' => 'TOPLITA',
        'prag_altitudine' => 1600,
        'meteoblue_peste_prag' => 'masivul-ceahlău_romania_682481',
        'meteoblue_sub_prag' => ['lat' => 46.988, 'lon' => 25.958],
        'altitudine_maxima' => 1907
    ],
    
    'hasmas' => [
        'anm_peste_prag' => 'CEAHLAU TOACA',
        'anm_sub_prag' => 'JOSENI',
        'prag_altitudine' => 1600,
        'meteoblue_peste_prag' => 'munţii-hăşmaş_romania_676284',
        'meteoblue_sub_prag' => ['lat' => 46.685, 'lon' => 25.826],
        'altitudine_maxima' => 1792
    ],
    
    'maramuresului' => [
        'anm_peste_prag' => 'IEZER',
        'anm_sub_prag' => 'SIGHETUL MARMATIEI',
        'prag_altitudine' => 1600,
        'meteoblue_peste_prag' => 'hora-pip-ivan_romania_506555',
        'meteoblue_sub_prag' => null, // Nu e disponibil
        'altitudine_maxima' => 1957
    ],
    
    'rodnei' => [
        'anm_peste_prag' => 'IEZER',
        'anm_sub_prag' => 'SIGHETUL MARMATIEI',
        'prag_altitudine' => 1800,
        'meteoblue_peste_prag' => 'pietrosul-rodnei_romania_8741063',
        'meteoblue_sub_prag' => ['lat' => 47.613, 'lon' => 24.856],
        'altitudine_maxima' => 2303
    ],
    
    // ═══════════════════════════════════════════════════════════════
    // 🌄 CARPAȚII OCCIDENTALI
    // ═══════════════════════════════════════════════════════════════
    
    'apuseni' => [
        'anm_peste_prag' => 'VLADEASA 1800',
        'anm_sub_prag' => 'BAISOARA',
        'prag_altitudine' => 1400,
        'meteoblue_peste_prag' => 'apuseni-mountains_romania_686257',
        'meteoblue_sub_prag' => 'băişoara_romania_685773',
        'altitudine_maxima' => 1849
    ],
    
    'mehedinti-cernei' => [
        'anm_peste_prag' => 'CUNTU',
        'anm_sub_prag' => 'BAILE HERCULANE',
        'prag_altitudine' => 1200,
        'meteoblue_peste_prag' => 'muntii-mehedinţi_romania_673611',
        'meteoblue_sub_prag' => ['lat' => 45.163, 'lon' => 22.699],
        'altitudine_maxima' => 1466
    ]
];