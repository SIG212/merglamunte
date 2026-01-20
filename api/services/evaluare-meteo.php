<?php
/**
 * Serviciu evaluare meteo pe 12 factori
 * Returnează status: VERDE / GALBEN / ROSU
 */

require_once __DIR__ . '/../utils/helpers.php';
require_once __DIR__ . '/../utils/calcule.php';

/**
 * Evaluează meteo pe 12 factori și returnează status agregat
 */
function evaluareMeteo($valori_meteo, $risc_avalansa_data) {
    $praguri = loadConfig('praguri-meteo');
    
    // 1. Evaluăm fiecare factor
    $categorii = [];
    
    // Factor 1: Stres termic (windchill)
    $categorii['stres_termic'] = evaluareWindchill($valori_meteo['windchill'], $praguri);
    
    // Factor 2: Vânt
    $categorii['vant'] = evaluareVant($valori_meteo['vant_kmh'], $praguri);
    
    // Factor 3: Vizibilitate
    $categorii['vizibilitate'] = evaluareVizibilitate($valori_meteo['fenomen'], $valori_meteo['nebulozitate']);
    
    // Factor 4-7: Precipitații (simplificat din fenomen)
    $categorii['precipitatii'] = evaluarePrecipitatii($valori_meteo['fenomen']);
    
    // Factor 8: Instabilitate atmosferică
    $categorii['instabilitate'] = evaluareInstabilitate();
    
    // Factor 9: Starea solului (zăpadă)
    $categorii['stare_sol'] = evaluareZapada($valori_meteo['zapada_cm'], $praguri);
    
    // Factor 10: Durata expunerii
    $categorii['durata_expunere'] = evaluareDurataExpunere($valori_meteo['temperatura'], $valori_meteo['vant_kmh']);
    
    // Factor 11: Schimbări rapide
    $categorii['schimbari_rapide'] = evaluareSchimbariRapide($valori_meteo['temperatura']);
    
    // Factor 12: Avalanșă
    $categorii['avalansa'] = evaluareAvalansa($risc_avalansa_data);
    
    // 2. Verificăm factori critici (care forțează ROȘU direct)
    $factor_critic = verificaFactoriCritici($categorii, $praguri);
    if ($factor_critic) {
        return [
            'status' => 'ROSU',
            'tip' => 'critic',
            'motiv' => $factor_critic['motiv'],
            'mesaj' => $factor_critic['mesaj'],
            'categorii' => $categorii,
            'no_go_count' => countByStatus($categorii, 'NO-GO'),
            'caution_count' => countByStatus($categorii, 'CAUTION')
        ];
    }
    
    // 3. Agregare status normal
    $no_go_count = countByStatus($categorii, 'NO-GO');
    $caution_count = countByStatus($categorii, 'CAUTION');
    
    $status = determineStatusAgregat($no_go_count, $caution_count, $praguri);
    
    return [
        'status' => $status,
        'tip' => 'normal',
        'categorii' => $categorii,
        'no_go_count' => $no_go_count,
        'caution_count' => $caution_count
    ];
}

/**
 * Evaluare windchill
 */
function evaluareWindchill($windchill, $praguri) {
    if ($windchill < $praguri['windchill']['NO-GO']['max']) {
        return [
            'status' => 'NO-GO',
            'valoare' => round($windchill, 1) . '°C',
            'mesaj' => 'Windchill extrem - risc hipotermie severă'
        ];
    } elseif ($windchill < $praguri['windchill']['CAUTION']['max']) {
        return [
            'status' => 'CAUTION',
            'valoare' => round($windchill, 1) . '°C',
            'mesaj' => 'Windchill foarte scăzut - risc hipotermie'
        ];
    } else {
        return [
            'status' => 'GO',
            'valoare' => round($windchill, 1) . '°C',
            'mesaj' => 'Stres termic acceptabil'
        ];
    }
}

/**
 * Evaluare vânt
 */
function evaluareVant($vant_kmh, $praguri) {
    if ($vant_kmh > $praguri['vant']['NO-GO']['min']) {
        return [
            'status' => 'NO-GO',
            'valoare' => round($vant_kmh) . ' km/h',
            'mesaj' => 'Vânt extrem de puternic - pericol pe creste'
        ];
    } elseif ($vant_kmh > $praguri['vant']['CAUTION']['min']) {
        return [
            'status' => 'CAUTION',
            'valoare' => round($vant_kmh) . ' km/h',
            'mesaj' => 'Vânt puternic - evitați crestele'
        ];
    } else {
        return [
            'status' => 'GO',
            'valoare' => round($vant_kmh) . ' km/h',
            'mesaj' => 'Vânt acceptabil'
        ];
    }
}

/**
 * Evaluare vizibilitate
 */
function evaluareVizibilitate($fenomen, $nebulozitate) {
    $fenomen_lower = strtolower($fenomen);
    $nebulozitate_lower = strtolower($nebulozitate);
    
    if (stripos($fenomen_lower, 'viscol') !== false || stripos($fenomen_lower, 'transport') !== false) {
        return [
            'status' => 'NO-GO',
            'valoare' => 'Viscol activ',
            'mesaj' => 'Vizibilitate zero - risc pierdere orientare'
        ];
    } elseif (stripos($fenomen_lower, 'ceata') !== false || stripos($fenomen_lower, 'cetos') !== false) {
        return [
            'status' => 'CAUTION',
            'valoare' => 'Ceață',
            'mesaj' => 'Vizibilitate redusă - atenție la orientare'
        ];
    } elseif (stripos($nebulozitate_lower, 'acoperit') !== false) {
        return [
            'status' => 'CAUTION',
            'valoare' => 'Cer acoperit',
            'mesaj' => 'Posibile schimbări meteo'
        ];
    } else {
        return [
            'status' => 'GO',
            'valoare' => 'Bună',
            'mesaj' => 'Vizibilitate bună'
        ];
    }
}

/**
 * Evaluare precipitații
 */
function evaluarePrecipitatii($fenomen) {
    $fenomen_lower = strtolower($fenomen);
    
    if (stripos($fenomen_lower, 'ninsoare') !== false) {
        if (stripos($fenomen_lower, 'abundent') !== false) {
            return [
                'status' => 'NO-GO',
                'valoare' => 'Ninsoare abundentă',
                'mesaj' => 'Acoperire marcaje - orientare dificilă'
            ];
        } else {
            return [
                'status' => 'CAUTION',
                'valoare' => 'Ninsoare moderată',
                'mesaj' => 'Ninsoare în curs'
            ];
        }
    } elseif (stripos($fenomen_lower, 'ploaie') !== false || stripos($fenomen_lower, 'lapovit') !== false) {
        return [
            'status' => 'CAUTION',
            'valoare' => 'Ploaie/Lapoviță',
            'mesaj' => 'Suprafețe alunecoase'
        ];
    } else {
        return [
            'status' => 'GO',
            'valoare' => 'Fără',
            'mesaj' => 'Fără precipitații'
        ];
    }
}

/**
 * Evaluare instabilitate atmosferică (furtuni vară)
 */
function evaluareInstabilitate() {
    $luna = intval(date('m'));
    $ora = intval(date('H'));
    
    if ($luna >= 6 && $luna <= 8 && $ora >= 13 && $ora <= 17) {
        return [
            'status' => 'CAUTION',
            'valoare' => 'Risc furtuni',
            'mesaj' => 'Perioada cu risc furtuni după-amiază (vară)'
        ];
    }
    
    return [
        'status' => 'GO',
        'valoare' => 'Stabilă',
        'mesaj' => 'Atmosferă stabilă'
    ];
}

/**
 * Evaluare strat zăpadă
 */
function evaluareZapada($zapada_cm, $praguri) {
    if ($zapada_cm > $praguri['zapada']['NO-GO']['min']) {
        return [
            'status' => 'NO-GO',
            'valoare' => $zapada_cm . ' cm',
            'mesaj' => 'Strat masiv - deplasare extrem de dificilă'
        ];
    } elseif ($zapada_cm > $praguri['zapada']['CAUTION']['min']) {
        return [
            'status' => 'CAUTION',
            'valoare' => $zapada_cm . ' cm',
            'mesaj' => 'Strat important - crampoane obligatorii'
        ];
    } else {
        return [
            'status' => 'GO',
            'valoare' => $zapada_cm . ' cm',
            'mesaj' => 'Strat acceptabil'
        ];
    }
}

/**
 * Evaluare durata expunere
 */
function evaluareDurataExpunere($temperatura, $vant_kmh) {
    if ($temperatura < -15 && $vant_kmh > 30) {
        return [
            'status' => 'CAUTION',
            'valoare' => 'Limitată',
            'mesaj' => 'Expunere prelungită periculoasă'
        ];
    }
    
    return [
        'status' => 'GO',
        'valoare' => 'OK',
        'mesaj' => 'Expunere acceptabilă'
    ];
}

/**
 * Evaluare schimbări rapide
 */
function evaluareSchimbariRapide($temperatura) {
    if ($temperatura > -2 && $temperatura < 2) {
        return [
            'status' => 'CAUTION',
            'valoare' => 'Temperatură la 0°C',
            'mesaj' => 'Posibile schimbări rapide'
        ];
    }
    
    return [
        'status' => 'GO',
        'valoare' => 'Stabil',
        'mesaj' => 'Condiții stabile'
    ];
}

/**
 * Evaluare risc avalanșă
 */
function evaluareAvalansa($risc_avalansa_data) {
    if (!$risc_avalansa_data['disponibil']) {
        return [
            'status' => 'GO',
            'valoare' => 'N/A',
            'mesaj' => 'Date avalanșă indisponibile',
            'validitate' => null
        ];
    }
    
    $risc = $risc_avalansa_data['risc'];
    $validitate = $risc_avalansa_data['validitate'];
    
    if ($risc >= 4) {
        return [
            'status' => 'NO-GO',
            'valoare' => $risc . '/5',
            'mesaj' => 'Risc avalanșă ridicat - evitați muntele',
            'validitate' => $validitate
        ];
    } elseif ($risc >= 3) {
        return [
            'status' => 'CAUTION',
            'valoare' => $risc . '/5',
            'mesaj' => 'Risc avalanșă însemnat - evitați pantele abrupte',
            'validitate' => $validitate
        ];
    } elseif ($risc >= 2) {
        return [
            'status' => 'CAUTION',
            'valoare' => $risc . '/5',
            'mesaj' => 'Risc avalanșă moderat',
            'validitate' => $validitate
        ];
    } else {
        return [
            'status' => 'GO',
            'valoare' => $risc . '/5',
            'mesaj' => 'Risc avalanșă scăzut',
            'validitate' => $validitate
        ];
    }
}

/**
 * Verifică factori critici care forțează ROȘU automat
 */
function verificaFactoriCritici($categorii, $praguri) {
    $critici = $praguri['factori_critici'];
    
    // Avalanșă 4+
    if ($categorii['avalansa']['status'] === 'NO-GO' && 
        strpos($categorii['avalansa']['valoare'], '/5') !== false) {
        $risc = intval($categorii['avalansa']['valoare']);
        if ($risc >= $critici['avalansa_risc']) {
            return [
                'motiv' => 'avalansa_critica',
                'mesaj' => 'Risc avalanșă extrem (4-5/5) - MUNTE ÎNCHIS'
            ];
        }
    }
    
    // Viscol
    if ($categorii['vizibilitate']['valoare'] === 'Viscol activ') {
        return [
            'motiv' => 'viscol',
            'mesaj' => 'Viscol activ - vizibilitate zero'
        ];
    }
    
    // Vânt > 80
    if ($categorii['vant']['status'] === 'NO-GO') {
        $vant = intval($categorii['vant']['valoare']);
        if ($vant >= $critici['vant_extrem']) {
            return [
                'motiv' => 'vant_extrem',
                'mesaj' => 'Vânt peste 80 km/h - pericol extrem'
            ];
        }
    }
    
    // Windchill < -35
    if ($categorii['stres_termic']['status'] === 'NO-GO') {
        $windchill = floatval($categorii['stres_termic']['valoare']);
        if ($windchill <= $critici['windchill_extrem']) {
            return [
                'motiv' => 'windchill_extrem',
                'mesaj' => 'Windchill sub -35°C - risc hipotermie severă'
            ];
        }
    }
    
    return null;
}

/**
 * Determină status agregat din numărul de factori NO-GO / CAUTION
 */
function determineStatusAgregat($no_go_count, $caution_count, $praguri) {
    $agregare = $praguri['agregare'];
    
    if ($no_go_count >= $agregare['no_go_min']) {
        return 'ROSU';
    }
    
    if ($caution_count >= $agregare['caution_min']) {
        return 'GALBEN';
    }
    
    return 'VERDE';
}
