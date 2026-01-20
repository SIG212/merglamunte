<?php
/**
 * Serviciu determinare context traseu
 * Determină: sezon, zonă (sub/peste prag), dificultate
 */

require_once __DIR__ . '/../utils/date-helpers.php';
require_once __DIR__ . '/../utils/helpers.php';

/**
 * Determină contextul complet al traseului
 */
function determinareContextTraseu($masiv_slug, $data, $altitudine_tinta, $zapada_cm = 0) {
    $masive = loadConfig('masive');
    
    if (!isset($masive[$masiv_slug])) {
        throw new Exception("Masiv invalid: $masiv_slug");
    }
    
    $masiv = $masive[$masiv_slug];
    
    // 1. Determină sezonul
    $sezon = determinareSezon($data, $zapada_cm);
    
    // 2. Determină zona (sub/peste prag)
    $zona = determinareZona($altitudine_tinta, $masiv['altitudine_prag']);
    
    // 3. Extrage dificultatea pentru zonă + sezon
    $dificultate = $masiv['dificultate'][$zona][$sezon];
    
    return [
        'masiv' => $masiv_slug,
        'masiv_display' => $masiv['nume_display'],
        'sezon' => $sezon,
        'zona' => $zona,
        'altitudine_tinta' => $altitudine_tinta,
        'altitudine_prag' => $masiv['altitudine_prag'],
        'dificultate' => $dificultate,
        'risc_general' => $masiv['risc_general'],
        'caracteristici' => $masiv['caracteristici']
    ];
}

/**
 * Determină dacă altitudinea este sub sau peste pragul masivului
 */
function determinareZona($altitudine, $altitudine_prag) {
    return $altitudine >= $altitudine_prag ? 'peste_prag' : 'sub_prag';
}

/**
 * Returnează explicațiile pentru masiv și zonă
 * Doar pentru decizii GALBEN sau ROSU
 */
function getExplicatiiMasiv($masiv_slug, $zona, $status_decizie) {
    // Nu returnăm explicații pentru VERDE
    if ($status_decizie === 'VERDE') {
        return null;
    }
    
    $explicatii = loadConfig('explicatii-masive');
    
    if (!isset($explicatii[$masiv_slug][$zona])) {
        return null;
    }
    
    $explicatie = $explicatii[$masiv_slug][$zona];
    
    return [
        'descriere' => $explicatie['descriere'] ?? '',
        'dificultati' => $explicatie['dificultati'] ?? '',
        'exemple_trasee' => $explicatie['exemple_trasee'] ?? ''
    ];
}

if (!function_exists('determinareContext')) {
    /**
     * Alias pentru determinareContextTraseu (pentru compatibilitate)
     */
    function determinareContext($masiv_slug, $data, $altitudine_tinta, $zapada_cm = 0) {
        $result = determinareContextTraseu($masiv_slug, $data, $altitudine_tinta, $zapada_cm);
        
        // Add alias pentru compatibilitate cu analiza-v2/v3
        $result['dificultate_grad'] = $result['dificultate'];
        
        return $result;
    }
}
