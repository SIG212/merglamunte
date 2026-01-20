<?php
/**
 * Serviciu Evaluare Factori Meteo - Complet
 * 
 * Evaluează toți factorii și returnează statusul pentru fiecare:
 * - Factori meteo (Meteoblue prognoză)
 * - Factori actuali (ANM)
 * - Buletin nivologic
 * - Cod vreme rea
 */

/**
 * Evaluare completă a tuturor factorilor
 */
/**
 * Determină dacă un factor GALBEN este SEVER
 * (afectează verdictul chiar dacă e singur)
 */
if (!function_exists('eFactorSever')) {
    function eFactorSever($nume_factor, $factor_data) {
        // Factor e deja ROȘU → nu verificăm aici
        if ($factor_data['status'] === 'ROSU') {
            return false; // Se gestionează separat prin no_go_count
        }
        
        // Factor e VERDE → nu e sever
        if ($factor_data['status'] !== 'GALBEN') {
            return false;
        }
        
        // FACTORI SEVERI - chiar dacă sunt GALBEN
        
        // 1. AVALANȘĂ 3+
        if ($nume_factor === 'risc_avalansa') {
            // Extrage nivelul din detalii (ex: "Risc 3/5")
            if (preg_match('/Risc\s+(\d)/', $factor_data['detalii'], $matches)) {
                $nivel = intval($matches[1]);
                if ($nivel >= 3) {
                    return true; // ⚠️ SEVER
                }
            }
        }
        
        // 2. COD METEO ACTIV (galben/portocaliu)
        if ($nume_factor === 'cod_meteo_activ') {
            return true; // ⚠️ SEVER - cod ANM e întotdeauna serios
        }
        
        // 3. VÂNT PUTERNIC (>50 km/h)
        if ($nume_factor === 'vant') {
            if (preg_match('/(\d+)\s*km\/h/', $factor_data['detalii'], $matches)) {
                $viteza = intval($matches[1]);
                if ($viteza >= 50) {
                    return true; // ⚠️ SEVER
                }
            }
        }
        
        // 4. WINDCHILL EXTREM (<-15°C)
        if ($nume_factor === 'stres_termic') {
            if (preg_match('/(-?\d+\.?\d*)\s*°C/', $factor_data['detalii'], $matches)) {
                $windchill = floatval($matches[1]);
                if ($windchill < -15) {
                    return true; // ⚠️ SEVER
                }
            }
        }
        
        // 5. VISCOL / VIZIBILITATE ZERO
        if ($nume_factor === 'vizibilitate') {
            if (stripos($factor_data['detalii'], 'viscol') !== false ||
                stripos($factor_data['detalii'], 'zero') !== false) {
                return true; // ⚠️ SEVER
            }
        }
        
        // 6. NINSOARE ABUNDENTĂ
        if ($nume_factor === 'precipitatii_ninsoare') {
            if (stripos($factor_data['detalii'], 'abundent') !== false) {
                return true; // ⚠️ SEVER
            }
        }
        
        // 7. STRAT ZĂPADĂ MASIV (>100cm)
        if ($nume_factor === 'stare_sol') {
            if (preg_match('/(\d+)\s*cm/', $factor_data['detalii'], $matches)) {
                $zapada = intval($matches[1]);
                if ($zapada > 100) {
                    return true; // ⚠️ SEVER
                }
            }
        }
        
        // Altfel, factor GALBEN normal (nu sever)
        return false;
    }
}
function evaluareFactoriComplet(
    $valori_meteo,
    $avalansa,
    $cod_vreme_rea,
    $detalii_zapada_buletin,
    $date_curente_anm,
    $context
) {
    $factori = [];
    $no_go_count = 0;
    $caution_count = 0;
    
    // ═══════════════════════════════════════════════════════════════
    // FACTORI METEO (Meteoblue prognoză sau ANM actual)
    // ═══════════════════════════════════════════════════════════════
    
    // 1. TEMPERATURĂ + REAL FEEL
    $temp = $valori_meteo['temperatura'] ?? null;
    $temp_resimtita = $valori_meteo['temperatura_resimtita'] ?? $valori_meteo['windchill'] ?? null;
    
    if ($temp !== null) {
        $factori['temperatura'] = evaluareTemperatura($temp, $temp_resimtita);
    }
    
    // 2. STRES TERMIC (windchill)
    if ($temp_resimtita !== null) {
        $factori['stres_termic'] = evaluareStresTermic($temp_resimtita);
    }
    
    // 3. VÂNT
    $vant = $valori_meteo['vant_viteza'] ?? $valori_meteo['vant'] ?? null;
    if ($vant !== null) {
        $factori['vant'] = evaluareVant($vant);
    }
    
    // 4. VIZIBILITATE (prognoză)
    $viz_prognoza = $valori_meteo['vizibilitate'] ?? null;
    if ($viz_prognoza !== null) {
        $factori['vizibilitate_prognoza'] = evaluareVizibilitate($viz_prognoza, 'prognoză');
    }
    
    // 5. PRECIPITAȚII MAX/ORĂ (interval 6-20)
    $precip_max_ora = $valori_meteo['precipitatii_max_ora'] ?? null;
    if ($precip_max_ora !== null) {
        $factori['precipitatii_max_ora'] = evaluarePrecipitatiiOra($precip_max_ora);
    }
    
    // 6. PRECIPITAȚII 24H
    $precip_24h = $valori_meteo['precipitatii_24h'] ?? $valori_meteo['precipitatii'] ?? null;
    if ($precip_24h !== null) {
        $factori['precipitatii_24h'] = evaluarePrecipitatii24h($precip_24h);
    }
    
    // 7. TIP PRECIPITAȚII
    $tip_precip = $valori_meteo['tip_precipitatii'] ?? null;
    if ($tip_precip !== null) {
        $factori['tip_precipitatii'] = evaluareTipPrecipitatii($tip_precip);
    }
    
    // 8. INSTABILITATE ATMOSFERICĂ
    $factori['instabilitate'] = evaluareInstabilitate($valori_meteo, $context);
    
    // ═══════════════════════════════════════════════════════════════
    // FACTORI ACTUALI (ANM - date în timp real)
    // ═══════════════════════════════════════════════════════════════
    
    // 9. VIZIBILITATE ACTUALĂ
    if ($date_curente_anm && isset($date_curente_anm['vizibilitate'])) {
        $factori['vizibilitate_actual'] = evaluareVizibilitateActuala(
            $date_curente_anm['vizibilitate'],
            $date_curente_anm['masurat_la'] ?? null,
            $date_curente_anm['statie'] ?? null
        );
    }
    
    // 10. GROSIME STRAT ZĂPADĂ
    if ($date_curente_anm && isset($date_curente_anm['zapada_cm'])) {
        $factori['grosime_zapada'] = evaluareGrosimeZapada(
            $date_curente_anm['zapada_cm'],
            $date_curente_anm['masurat_la'] ?? null,
            $date_curente_anm['statie'] ?? null
        );
    }
    
    // ═══════════════════════════════════════════════════════════════
    // BULETIN NIVOLOGIC
    // ═══════════════════════════════════════════════════════════════
    
    // 11. RISC AVALANȘĂ
    $factori['avalansa'] = evaluareAvalansa($avalansa);
    
    // 12. DETALII STRAT ZĂPADĂ (din buletin)
    if ($detalii_zapada_buletin) {
        $factori['detalii_zapada'] = evaluareDetaliiZapada($detalii_zapada_buletin, $avalansa['valabilitate']);
    }
    
    // ═══════════════════════════════════════════════════════════════
    // COD VREME REA (ANM Nowcasting)
    // ═══════════════════════════════════════════════════════════════
    
    // 13. COD VREME REA
    if ($cod_vreme_rea && $cod_vreme_rea['activ']) {
        $factori['cod_vreme_rea'] = evaluareCodVremeRea($cod_vreme_rea);
    }
    
    // ═══════════════════════════════════════════════════════════════
    // CALCUL STATISTICI
    // ═══════════════════════════════════════════════════════════════
    
    foreach ($factori as $factor) {
        if ($factor['status'] === 'NO-GO') {
            $no_go_count++;
        } elseif ($factor['status'] === 'CAUTION') {
            $caution_count++;
        }
    }
    
    // Numără factori severi
    $factori_severi_count = 0;
    foreach ($factori as $nume => $factor) {
        if (eFactorSever($nume, $factor)) {
            $factori_severi_count++;
        }
    }
    
    // DETERMINARE STATUS METEO (cu logica SEVERI)
    if ($no_go_count >= 1) {
        $meteo_status = 'ROSU';
    } elseif ($factori_severi_count >= 1 || $caution_count >= 2) {
        // ⚠️ LOGICA NOUĂ:
        // - Orice factor SEVER → GALBEN
        // - SAU 2+ factori GALBEN normali → GALBEN
        $meteo_status = 'GALBEN';
    } else {
        $meteo_status = 'VERDE';
    }
    
    return [
        'factori' => $factori,
        'no_go_count' => $no_go_count,
        'caution_count' => $caution_count,
        'factori_severi_count' => $factori_severi_count, // ← NOU
        'meteo_status' => $meteo_status
    ];


// ═══════════════════════════════════════════════════════════════
// FUNCȚII EVALUARE INDIVIDUALĂ
// ═══════════════════════════════════════════════════════════════

function evaluareTemperatura($temp, $temp_resimtita = null) {
    $valoare = round($temp) . '°C';
    if ($temp_resimtita !== null && abs($temp - $temp_resimtita) > 3) {
        $valoare .= ' (resimțită ' . round($temp_resimtita) . '°C)';
    }
    
    if ($temp < -25) {
        return ['status' => 'NO-GO', 'valoare' => $valoare, 'mesaj' => 'Temperatură extrem de scăzută - risc hipotermie severă'];
    } elseif ($temp < -15) {
        return ['status' => 'CAUTION', 'valoare' => $valoare, 'mesaj' => 'Temperatură foarte scăzută - echipament termic obligatoriu'];
    } elseif ($temp > 35) {
        return ['status' => 'NO-GO', 'valoare' => $valoare, 'mesaj' => 'Temperatură extrem de ridicată - risc insolație'];
    } elseif ($temp > 30) {
        return ['status' => 'CAUTION', 'valoare' => $valoare, 'mesaj' => 'Temperatură ridicată - hidratare frecventă'];
    }
    return ['status' => 'GO', 'valoare' => $valoare, 'mesaj' => 'Temperatură acceptabilă'];
}

function evaluareStresTermic($windchill) {
    $valoare = round($windchill) . '°C';
    
    if ($windchill < -30) {
        return ['status' => 'NO-GO', 'valoare' => $valoare, 'mesaj' => 'Windchill extrem - degerături în minute'];
    } elseif ($windchill < -20) {
        return ['status' => 'CAUTION', 'valoare' => $valoare, 'mesaj' => 'Windchill sever - expunere limitată'];
    }
    return ['status' => 'GO', 'valoare' => $valoare, 'mesaj' => 'Stres termic acceptabil'];
}

function evaluareVant($viteza_kmh) {
    $valoare = round($viteza_kmh) . ' km/h';
    
    if ($viteza_kmh > 70) {
        return ['status' => 'NO-GO', 'valoare' => $valoare, 'mesaj' => 'Vânt extrem - pericol pe creste, risc răsturnare'];
    } elseif ($viteza_kmh > 50) {
        return ['status' => 'CAUTION', 'valoare' => $valoare, 'mesaj' => 'Vânt puternic - evitați crestele expuse'];
    } elseif ($viteza_kmh > 35) {
        return ['status' => 'CAUTION', 'valoare' => $valoare, 'mesaj' => 'Vânt moderat-puternic - atenție pe porțiuni expuse'];
    }
    return ['status' => 'GO', 'valoare' => $valoare, 'mesaj' => 'Vânt acceptabil'];
}

function evaluareVizibilitate($viz, $tip = 'prognoză') {
    // viz poate fi: 'buna', 'moderata', 'redusa', 'foarte_redusa', sau valoare în km
    
    if (is_numeric($viz)) {
        if ($viz < 0.1) {
            return ['status' => 'NO-GO', 'valoare' => 'Sub 100m', 'mesaj' => 'Vizibilitate zero - risc rătăcire', 'sursa' => $tip];
        } elseif ($viz < 0.5) {
            return ['status' => 'CAUTION', 'valoare' => round($viz * 1000) . 'm', 'mesaj' => 'Vizibilitate foarte redusă', 'sursa' => $tip];
        } elseif ($viz < 2) {
            return ['status' => 'CAUTION', 'valoare' => round($viz, 1) . ' km', 'mesaj' => 'Vizibilitate redusă', 'sursa' => $tip];
        }
        return ['status' => 'GO', 'valoare' => round($viz) . ' km', 'mesaj' => 'Vizibilitate bună', 'sursa' => $tip];
    }
    
    $viz_lower = strtolower($viz);
    if (strpos($viz_lower, 'zero') !== false || strpos($viz_lower, 'nul') !== false) {
        return ['status' => 'NO-GO', 'valoare' => 'Zero', 'mesaj' => 'Vizibilitate nulă - pericol rătăcire', 'sursa' => $tip];
    } elseif (strpos($viz_lower, 'foarte') !== false && strpos($viz_lower, 'redus') !== false) {
        return ['status' => 'NO-GO', 'valoare' => 'Foarte redusă', 'mesaj' => 'Vizibilitate foarte redusă', 'sursa' => $tip];
    } elseif (strpos($viz_lower, 'redus') !== false || strpos($viz_lower, 'slab') !== false) {
        return ['status' => 'CAUTION', 'valoare' => 'Redusă', 'mesaj' => 'Vizibilitate redusă - atenție la orientare', 'sursa' => $tip];
    }
    return ['status' => 'GO', 'valoare' => 'Bună', 'mesaj' => 'Vizibilitate bună', 'sursa' => $tip];
}

function evaluareVizibilitateActuala($viz, $masurat_la, $statie) {
    $result = evaluareVizibilitate($viz, 'actuală');
    $result['masurat_la'] = $masurat_la;
    $result['sursa'] = 'ANM - ' . ($statie ?? 'stație meteo');
    return $result;
}

function evaluarePrecipitatiiOra($mm_ora) {
    $valoare = round($mm_ora, 1) . ' mm/h';
    
    if ($mm_ora > 10) {
        return ['status' => 'NO-GO', 'valoare' => $valoare, 'mesaj' => 'Precipitații torențiale - risc viituri'];
    } elseif ($mm_ora > 5) {
        return ['status' => 'CAUTION', 'valoare' => $valoare, 'mesaj' => 'Precipitații intense - suprafețe alunecoase'];
    } elseif ($mm_ora > 2) {
        return ['status' => 'CAUTION', 'valoare' => $valoare, 'mesaj' => 'Precipitații moderate'];
    }
    return ['status' => 'GO', 'valoare' => $valoare, 'mesaj' => 'Precipitații slabe sau absente'];
}

function evaluarePrecipitatii24h($mm_24h) {
    $valoare = round($mm_24h, 1) . ' mm/24h';
    
    if ($mm_24h > 30) {
        return ['status' => 'NO-GO', 'valoare' => $valoare, 'mesaj' => 'Precipitații abundente - condiții dificile'];
    } elseif ($mm_24h > 15) {
        return ['status' => 'CAUTION', 'valoare' => $valoare, 'mesaj' => 'Precipitații moderate-abundente'];
    } elseif ($mm_24h > 5) {
        return ['status' => 'CAUTION', 'valoare' => $valoare, 'mesaj' => 'Precipitații moderate'];
    }
    return ['status' => 'GO', 'valoare' => $valoare, 'mesaj' => 'Precipitații slabe sau fără'];
}

function evaluareTipPrecipitatii($tip) {
    $tip_lower = strtolower($tip);
    
    if (strpos($tip_lower, 'lapovit') !== false || strpos($tip_lower, 'ploaie îngheț') !== false) {
        return ['status' => 'CAUTION', 'valoare' => 'Lapoviță', 'mesaj' => 'Suprafețe foarte alunecoase - crampoane necesare'];
    } elseif (strpos($tip_lower, 'ninsoare') !== false) {
        return ['status' => 'CAUTION', 'valoare' => 'Ninsoare', 'mesaj' => 'Acoperire marcaje posibilă'];
    } elseif (strpos($tip_lower, 'ploaie') !== false) {
        return ['status' => 'CAUTION', 'valoare' => 'Ploaie', 'mesaj' => 'Echipament impermeabil necesar'];
    }
    return ['status' => 'GO', 'valoare' => 'Fără precipitații', 'mesaj' => 'Condiții uscate'];
}

function evaluareInstabilitate($valori_meteo, $context) {
    $luna = intval(date('m', strtotime($context['data'] ?? 'now')));
    $ora_curenta = intval(date('H'));
    
    // Vara, după-amiaza = risc furtuni
    if ($luna >= 5 && $luna <= 9) {
        // Verifică dacă e ora de risc (13-19)
        if ($ora_curenta >= 13 && $ora_curenta <= 19) {
            return ['status' => 'CAUTION', 'valoare' => 'Risc furtuni', 'mesaj' => 'Perioada cu risc crescut de furtuni (după-amiază vara)'];
        }
        return ['status' => 'CAUTION', 'valoare' => 'Monitorizare', 'mesaj' => 'Sezon cu instabilitate - monitorizați cerul'];
    }
    
    return ['status' => 'GO', 'valoare' => 'Stabilă', 'mesaj' => 'Atmosferă stabilă'];
}

function evaluareGrosimeZapada($cm, $masurat_la, $statie) {
    $valoare = $cm . ' cm';
    
    $result = [
        'valoare' => $valoare,
        'masurat_la' => $masurat_la,
        'sursa' => 'ANM - ' . ($statie ?? 'stație meteo')
    ];
    
    if ($cm > 200) {
        return array_merge($result, ['status' => 'NO-GO', 'mesaj' => 'Strat foarte gros - deplasare extrem de dificilă']);
    } elseif ($cm > 120) {
        return array_merge($result, ['status' => 'CAUTION', 'mesaj' => 'Strat gros - echipament de iarnă complet obligatoriu']);
    } elseif ($cm > 50) {
        return array_merge($result, ['status' => 'CAUTION', 'mesaj' => 'Strat consistent - crampoane și bețe recomandate']);
    } elseif ($cm > 0) {
        return array_merge($result, ['status' => 'GO', 'mesaj' => 'Strat subțire de zăpadă']);
    }
    return array_merge($result, ['status' => 'GO', 'mesaj' => 'Fără zăpadă']);
}

function evaluareAvalansa($avalansa) {
    $risc = $avalansa['risc'] ?? 0;
    $valoare = $risc . '/5 - ' . ($avalansa['nivel_text'] ?? 'Necunoscut');
    
    $result = [
        'valoare' => $valoare,
        'valabilitate' => $avalansa['valabilitate'] ?? null,
        'sursa' => $avalansa['sursa'] ?? 'Buletin Nivologic ANM'
    ];
    
    if ($avalansa['descriere']) {
        $result['detalii_extinse'] = $avalansa['descriere'];
    }
    
    if ($risc >= 4) {
        return array_merge($result, ['status' => 'NO-GO', 'mesaj' => 'Risc avalanșă ridicat/foarte ridicat - evitați zona montană']);
    } elseif ($risc === 3) {
        return array_merge($result, ['status' => 'CAUTION', 'mesaj' => 'Risc avalanșă însemnat - evitați pantele abrupte']);
    } elseif ($risc === 2) {
        return array_merge($result, ['status' => 'CAUTION', 'mesaj' => 'Risc avalanșă moderat - atenție la zonele expuse']);
    }
    return array_merge($result, ['status' => 'GO', 'mesaj' => 'Risc avalanșă scăzut']);
}

function evaluareDetaliiZapada($detalii, $valabilitate) {
    $mesaj_parts = [];
    
    if (!empty($detalii['detalii_altitudine'])) {
        $mesaj_parts[] = $detalii['detalii_altitudine'];
    }
    
    if (!empty($detalii['stare'])) {
        $stare_labels = [
            'tasata' => 'Zăpadă tasată',
            'afanata' => 'Zăpadă afânată',
            'compacta' => 'Zăpadă compactă',
            'umeda' => 'Zăpadă umedă',
            'inghetata' => 'Crustă de gheață',
            'instabila' => 'Strat instabil'
        ];
        $mesaj_parts[] = $stare_labels[$detalii['stare']] ?? $detalii['stare'];
    }
    
    // Determină status bazat pe pericole
    $status = 'GO';
    if (!empty($detalii['pericole'])) {
        $pericole_grave = ['placi_vant', 'strat_slab', 'avalanse_spontane'];
        foreach ($detalii['pericole'] as $pericol) {
            if (in_array($pericol, $pericole_grave)) {
                $status = 'CAUTION';
                break;
            }
        }
    }
    
    if ($detalii['stare'] === 'instabila') {
        $status = 'CAUTION';
    }
    
    return [
        'status' => $status,
        'valoare' => $detalii['descriere_generala'] ?: 'Vezi detalii',
        'mesaj' => implode('. ', $mesaj_parts) ?: 'Informații din buletinul nivologic',
        'detalii_extinse' => $detalii['detalii_altitudine'],
        'pericole' => $detalii['pericole'] ?? [],
        'valabilitate' => $valabilitate,
        'sursa' => 'Buletin Nivologic ANM'
    ];
}

function evaluareCodVremeRea($cod_data) {
    $cod = $cod_data['cod'];
    $display = $cod_data['cod_display'];
    
    $result = [
        'valoare' => $display['text'],
        'valabilitate' => $cod_data['valabilitate'],
        'sursa' => 'ANM Nowcasting'
    ];
    
    // Adaugă fenomenele ca detalii
    if (!empty($cod_data['fenomene'])) {
        $fenomen_labels = [
            'viscol' => 'Viscol',
            'ninsoare' => 'Ninsoare',
            'vant' => 'Vânt puternic',
            'polei' => 'Polei',
            'vizibilitate_redusa' => 'Vizibilitate redusă',
            'avalansa' => 'Risc avalanșă',
            'ploaie' => 'Ploaie',
            'furtuna' => 'Furtună',
            'grindina' => 'Grindină'
        ];
        
        $fenomene_text = [];
        foreach ($cod_data['fenomene'] as $key => $val) {
            if (is_string($val)) {
                $fenomene_text[] = $fenomen_labels[$val] ?? $val;
            } elseif ($key === 'vant_viteza' && is_array($val)) {
                $fenomene_text[] = "Rafale {$val['min']}-{$val['max']} km/h";
            }
        }
        $result['detalii_extinse'] = implode(', ', $fenomene_text);
    }
    
    if ($cod === 'rosu') {
        return array_merge($result, [
            'status' => 'NO-GO',
            'mesaj' => 'Cod ROȘU activ - condiții extreme, NU plecați în munte!'
        ]);
    } elseif ($cod === 'portocaliu') {
        return array_merge($result, [
            'status' => 'NO-GO',
            'mesaj' => 'Cod PORTOCALIU activ - condiții periculoase'
        ]);
    } elseif ($cod === 'galben') {
        return array_merge($result, [
            'status' => 'CAUTION',
            'mesaj' => 'Cod GALBEN activ - atenție sporită necesară'
        ]);
    }
    
    return array_merge($result, [
        'status' => 'GO',
        'mesaj' => 'Fără avertizări meteo active'
    ]);
}
