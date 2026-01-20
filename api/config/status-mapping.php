<?php
/**
 * Mapping status intern → UI display
 * Folosit pentru a transforma VERDE/GALBEN/ROSU în text user-friendly
 */

return [
    'VERDE' => [
        'text' => 'Condiții bune',
        'text_scurt' => 'Bune',
        'culoare' => 'verde',
        'culoare_hex' => '#10b981',
        'icon' => '🟢',
        'cod_decizie' => 'GO',
        'decizie_text' => 'Puteți merge',
        'class_css' => 'bg-green-50 border-green-500 text-green-900'
    ],
    
    'GALBEN' => [
        'text' => 'Condiții dificile',
        'text_scurt' => 'Dificile',
        'culoare' => 'galben',
        'culoare_hex' => '#f59e0b',
        'icon' => '🟡',
        'cod_decizie' => 'CAUTION',
        'decizie_text' => 'Aveți grijă',
        'class_css' => 'bg-yellow-50 border-yellow-500 text-yellow-900'
    ],
    
    'ROSU' => [
        'text' => 'Condiții foarte periculoase',
        'text_scurt' => 'Periculoase',
        'culoare' => 'rosu',
        'culoare_hex' => '#ef4444',
        'icon' => '🔴',
        'cod_decizie' => 'NO-GO',
        'decizie_text' => 'Nu mergeți',
        'class_css' => 'bg-red-50 border-red-500 text-red-900'
    ]
];
