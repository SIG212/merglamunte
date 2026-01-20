<?php
/**
 * Caracteristici masive montane
 * Sursa: Sheet caracteristici masive
 */

return [
    'bucegi' => [
        'nume_display' => 'Bucegi',
        'altitudine_maxima' => 2500,
        'altitudine_prag' => 1800,
        'dificultate' => [
            'sub_prag' => ['vara' => 2, 'iarna' => 3],
            'peste_prag' => ['vara' => 3, 'iarna' => 5]
        ],
        'risc_general' => 4,
        'caracteristici' => [
            'surse_apa' => false,
            'zone_expuse' => true,
            'avalanse' => true,
            'zapada_problematica' => true
        ],
        'descriere' => 'În Bucegi întâlnești des vânt violent, ceață, orme (iarnă) și zone abrupte.'
    ],
    
    'fagaras' => [
        'nume_display' => 'Făgăraș',
        'altitudine_maxima' => 2500,
        'altitudine_prag' => 1600,
        'dificultate' => [
            'sub_prag' => ['vara' => 2, 'iarna' => 2],
            'peste_prag' => ['vara' => 4, 'iarna' => 5]
        ],
        'risc_general' => 4,
        'caracteristici' => [
            'surse_apa' => false,
            'zone_expuse' => true,
            'avalanse' => true,
            'zapada_problematica' => true
        ],
        'descriere' => 'Masivul are creste lungi și expuse, retrageri dificile și vreme imprevizibilă.'
    ],
    
    'retezat' => [
        'nume_display' => 'Retezat',
        'altitudine_maxima' => 2500,
        'altitudine_prag' => 1600,
        'dificultate' => [
            'sub_prag' => ['vara' => 2, 'iarna' => 2],
            'peste_prag' => ['vara' => 4, 'iarna' => 5]
        ],
        'risc_general' => 4,
        'caracteristici' => [
            'surse_apa' => true,
            'zone_expuse' => true,
            'avalanse' => true,
            'zapada_problematica' => true
        ],
        'descriere' => 'Retezat are groho liș, muchii expuse și vreme aspină, cu pericol ridicat de accidente.'
    ],
    
    'piatra-craiului' => [
        'nume_display' => 'Piatra Craiului',
        'altitudine_maxima' => 2000,
        'altitudine_prag' => 1400,
        'dificultate' => [
            'sub_prag' => ['vara' => 3, 'iarna' => 3],
            'peste_prag' => ['vara' => 5, 'iarna' => 5]
        ],
        'risc_general' => 5,
        'caracteristici' => [
            'surse_apa' => false,
            'zone_expuse' => true,
            'avalanse' => true,
            'zapada_problematica' => true
        ],
        'descriere' => 'Piatra Craiului are creastă îngustă, prăpăstii și retrageri aproape imposibile, chiar și vara. E cel mai periculos.'
    ],
    
    'rodnei' => [
        'nume_display' => 'Rodnei',
        'altitudine_maxima' => 2100,
        'altitudine_prag' => 1800,
        'dificultate' => [
            'sub_prag' => ['vara' => 2, 'iarna' => 2],
            'peste_prag' => ['vara' => 3, 'iarna' => 5]
        ],
        'risc_general' => 3,
        'caracteristici' => [
            'surse_apa' => true,
            'zone_expuse' => true,
            'avalanse' => false,
            'zapada_problematica' => true
        ],
        'descriere' => 'Rodnei prezintă creste înalte, abrupți locali și teren alpin, cu retrageri sunt relativ sigure.'
    ],
    
    'bistritei' => [
        'nume_display' => 'Bistriței',
        'altitudine_maxima' => 1900,
        'altitudine_prag' => 1700,
        'dificultate' => [
            'sub_prag' => ['vara' => 2, 'iarna' => 3],
            'peste_prag' => ['vara' => 2, 'iarna' => 3]
        ],
        'risc_general' => 2,
        'caracteristici' => [
            'surse_apa' => true,
            'zone_expuse' => false,
            'avalanse' => false,
            'zapada_problematica' => false
        ],
        'descriere' => 'Munții Bistriței au relief domol, păduri extinse și risc redus.'
    ],
    
    'ceahlau' => [
        'nume_display' => 'Ceahlău',
        'altitudine_maxima' => 1904,
        'altitudine_prag' => 1800,
        'dificultate' => [
            'sub_prag' => ['vara' => 2, 'iarna' => 3],
            'peste_prag' => ['vara' => 2, 'iarna' => 3]
        ],
        'risc_general' => 3,
        'caracteristici' => [
            'surse_apa' => true,
            'zone_expuse' => true,
            'avalanse' => false,
            'zapada_problematica' => false
        ],
        'descriere' => 'Ceahlău prezintă stâncării și poteci clare, cu riscuri moderate.'
    ],
    
    'ciucas' => [
        'nume_display' => 'Ciucaș',
        'altitudine_maxima' => 1954,
        'altitudine_prag' => 1600,
        'dificultate' => [
            'sub_prag' => ['vara' => 2, 'iarna' => 3],
            'peste_prag' => ['vara' => 2, 'iarna' => 3]
        ],
        'risc_general' => 2,
        'caracteristici' => [
            'surse_apa' => true,
            'zone_expuse' => true,
            'avalanse' => false,
            'zapada_problematica' => false
        ],
        'descriere' => 'Ciucaș și Piatra Mare au abrupturi locale, dar platouri și poteci sigure.'
    ],
    
    // Alias pentru ciucas-piatra-mare
    'ciucas-piatra-mare' => [
        'nume_display' => 'Ciucaș, Piatra Mare',
        'altitudine_maxima' => 1954,
        'altitudine_prag' => 1600,
        'dificultate' => [
            'sub_prag' => ['vara' => 2, 'iarna' => 3],
            'peste_prag' => ['vara' => 2, 'iarna' => 3]
        ],
        'risc_general' => 2,
        'caracteristici' => [
            'surse_apa' => true,
            'zone_expuse' => true,
            'avalanse' => false,
            'zapada_problematica' => false
        ],
        'descriere' => 'Ciucaș și Piatra Mare au abrupturi locale, dar platouri și poteci sigure.'
    ],
    
    'calimani' => [
        'nume_display' => 'Călimani',
        'altitudine_maxima' => 2100,
        'altitudine_prag' => 1800,
        'dificultate' => [
            'sub_prag' => ['vara' => 2, 'iarna' => 3],
            'peste_prag' => ['vara' => 3, 'iarna' => 5]
        ],
        'risc_general' => 3,
        'caracteristici' => [
            'surse_apa' => false,
            'zone_expuse' => true,
            'avalanse' => true,
            'zapada_problematica' => true
        ],
        'descriere' => 'Călimani are pășuni alpine, crește zone expuse, dar cu posibile zăpezi problematice chiar și vara.'
    ],
    
    'hasmas' => [
        'nume_display' => 'Hășmaș',
        'altitudine_maxima' => 1700,
        'altitudine_prag' => 1700,
        'dificultate' => [
            'sub_prag' => ['vara' => 2, 'iarna' => 2],
            'peste_prag' => ['vara' => 2, 'iarna' => 2]
        ],
        'risc_general' => 2,
        'caracteristici' => [
            'surse_apa' => true,
            'zone_expuse' => false,
            'avalanse' => false,
            'zapada_problematica' => false
        ],
        'descriere' => 'Hășmaș este mai domol și poteci line și expunere redusă.'
    ],
    
    'maramuresului' => [
        'nume_display' => 'Maramureșului',
        'altitudine_maxima' => 1900,
        'altitudine_prag' => 1800,
        'dificultate' => [
            'sub_prag' => ['vara' => 3, 'iarna' => 4],
            'peste_prag' => ['vara' => 3, 'iarna' => 4]
        ],
        'risc_general' => 3,
        'caracteristici' => [
            'surse_apa' => true,
            'zone_expuse' => true,
            'avalanse' => true,
            'zapada_problematica' => true
        ],
        'descriere' => 'Maramureșului are abrupturi localedar retrageri sunt relativ sigure.'
    ],
    
    'parang' => [
        'nume_display' => 'Parâng',
        'altitudine_maxima' => 2500,
        'altitudine_prag' => 2000,
        'dificultate' => [
            'sub_prag' => ['vara' => 3, 'iarna' => 5],
            'peste_prag' => ['vara' => 3, 'iarna' => 5]
        ],
        'risc_general' => 3,
        'caracteristici' => [
            'surse_apa' => true,
            'zone_expuse' => true,
            'avalanse' => true,
            'zapada_problematica' => true
        ],
        'descriere' => 'Parângul este alpin și expus, Sureanu mai domol și cu poteci largi.'
    ],
    
    // Alias pentru parang-sureanu
    'parang-sureanu' => [
        'nume_display' => 'Parâng, Șureanu',
        'altitudine_maxima' => 2500,
        'altitudine_prag' => 2000,
        'dificultate' => [
            'sub_prag' => ['vara' => 3, 'iarna' => 5],
            'peste_prag' => ['vara' => 3, 'iarna' => 5]
        ],
        'risc_general' => 3,
        'caracteristici' => [
            'surse_apa' => true,
            'zone_expuse' => true,
            'avalanse' => true,
            'zapada_problematica' => true
        ],
        'descriere' => 'Parângul este alpin și expus, Șureanu mai domol și cu poteci largi.'
    ],
    
    'tarcu' => [
        'nume_display' => 'Tarcu',
        'altitudine_maxima' => 2186,
        'altitudine_prag' => 1800,
        'dificultate' => [
            'sub_prag' => ['vara' => 3, 'iarna' => 4],
            'peste_prag' => ['vara' => 3, 'iarna' => 4]
        ],
        'risc_general' => 2,
        'caracteristici' => [
            'surse_apa' => true,
            'zone_expuse' => true,
            'avalanse' => true,
            'zapada_problematica' => true
        ],
        'descriere' => 'Tarcu și Godeanu sunt înalți, cu creste rotunjite și risc moderat.'
    ],
    
    'godeanu' => [
        'nume_display' => 'Godeanu',
        'altitudine_maxima' => 1885,
        'altitudine_prag' => 1800,
        'dificultate' => [
            'sub_prag' => ['vara' => 2, 'iarna' => 4],
            'peste_prag' => ['vara' => 2, 'iarna' => 4]
        ],
        'risc_general' => 2,
        'caracteristici' => [
            'surse_apa' => true,
            'zone_expuse' => true,
            'avalanse' => true,
            'zapada_problematica' => true
        ],
        'descriere' => 'Buila are calcar, trasee scurte și expunere semibilitică chiar și vara. E este bland.'
    ],
    
    'cozia' => [
        'nume_display' => 'Cozia',
        'altitudine_maxima' => 1668,
        'altitudine_prag' => 1668,
        'dificultate' => [
            'sub_prag' => ['vara' => 2, 'iarna' => 2],
            'peste_prag' => ['vara' => 2, 'iarna' => 2]
        ],
        'risc_general' => 1,
        'caracteristici' => [
            'surse_apa' => true,
            'zone_expuse' => false,
            'avalanse' => false,
            'zapada_problematica' => false
        ],
        'descriere' => 'Cozia are poteci clare și abrupturi scurte, relativ sigure.'
    ],
    
    'apuseni' => [
        'nume_display' => 'Apuseni',
        'altitudine_maxima' => 1884,
        'altitudine_prag' => 1500,
        'dificultate' => [
            'sub_prag' => ['vara' => 2, 'iarna' => 3],
            'peste_prag' => ['vara' => 2, 'iarna' => 3]
        ],
        'risc_general' => 1,
        'caracteristici' => [
            'surse_apa' => true,
            'zone_expuse' => false,
            'avalanse' => false,
            'zapada_problematica' => false
        ],
        'descriere' => 'Apuseni au păduri, carst și cabane frecvente, fiind cel mai sigur masiv iarna.'
    ],
    
    'mehedinti-cernei' => [
        'nume_display' => 'Mehedinți, Cernei',
        'altitudine_maxima' => 1900,
        'altitudine_prag' => 1600,
        'dificultate' => [
            'sub_prag' => ['vara' => 2, 'iarna' => 3],
            'peste_prag' => ['vara' => 3, 'iarna' => 4]
        ],
        'risc_general' => 2,
        'caracteristici' => [
            'surse_apa' => true,
            'zone_expuse' => true,
            'avalanse' => false,
            'zapada_problematica' => false
        ],
        'descriere' => 'Munții Mehedinți și Cernei au relief calcaros, chei spectaculoase și risc moderat.'
    ],
    
    'buila' => [
        'nume_display' => 'Buila-Vânturariţa',
        'altitudine_maxima' => 1849,
        'altitudine_prag' => 1600,
        'dificultate' => [
            'sub_prag' => ['vara' => 2, 'iarna' => 3],
            'peste_prag' => ['vara' => 3, 'iarna' => 4]
        ],
        'risc_general' => 2,
        'caracteristici' => [
            'surse_apa' => true,
            'zone_expuse' => true,
            'avalanse' => false,
            'zapada_problematica' => false
        ],
        'descriere' => 'Buila are peisaje calcaroase, trasee scurte și expunere moderată.'
    ],
    
    'iezer' => [
        'nume_display' => 'Iezer-Păpușa',
        'altitudine_maxima' => 2509,
        'altitudine_prag' => 1800,
        'dificultate' => [
            'sub_prag' => ['vara' => 2, 'iarna' => 3],
            'peste_prag' => ['vara' => 4, 'iarna' => 5]
        ],
        'risc_general' => 4,
        'caracteristici' => [
            'surse_apa' => true,
            'zone_expuse' => true,
            'avalanse' => true,
            'zapada_problematica' => true
        ],
        'descriere' => 'Iezer-Păpușa are relief alpin sever, creste expuse și risc avalanșe.'
    ],
    
    'baiului' => [
        'nume_display' => 'Baiului',
        'altitudine_maxima' => 1885,
        'altitudine_prag' => 1600,
        'dificultate' => [
            'sub_prag' => ['vara' => 2, 'iarna' => 2],
            'peste_prag' => ['vara' => 2, 'iarna' => 3]
        ],
        'risc_general' => 2,
        'caracteristici' => [
            'surse_apa' => true,
            'zone_expuse' => false,
            'avalanse' => false,
            'zapada_problematica' => false
        ],
        'descriere' => 'Baiului are relief moderat, păduri și trasee sigure.'
    ],
    
    'cindrel' => [
        'nume_display' => 'Cindrel',
        'altitudine_maxima' => 2244,
        'altitudine_prag' => 1800,
        'dificultate' => [
            'sub_prag' => ['vara' => 2, 'iarna' => 3],
            'peste_prag' => ['vara' => 3, 'iarna' => 4]
        ],
        'risc_general' => 2,
        'caracteristici' => [
            'surse_apa' => true,
            'zone_expuse' => true,
            'avalanse' => false,
            'zapada_problematica' => true
        ],
        'descriere' => 'Cindrel prezintă platouri alpine și creste rotunjite, cu risc moderat.'
    ]
];
