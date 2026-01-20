<?php
/**
 * API Analiză V3 - Sistem GO / CAUTION / NO-GO
 * MergLaMunte.ro
 * 
 * Integrează:
 * - ANM pentru date actuale (stații meteo)
 * - Meteoblue pentru prognoză
 * - Buletin Nivologic (risc avalanșă + detalii zăpadă)
 * - ANM Nowcasting (cod vreme rea)
 * - Matrice de risc (nivel experiență × dificultate × meteo)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ═══════════════════════════════════════════════════════════════
// CONFIGURARE
// ═══════════════════════════════════════════════════════════════

define('METEOBLUE_API_KEY', 'cljvDyWgqXQe4T1x');

// Include servicii și utilități
$base_path = __DIR__;

// Config
if (file_exists($base_path . '/config/statii-meteo.php')) {
    // Nu facem require aici - e un return array
}
if (file_exists($base_path . '/config/masive.php')) {
    require_once $base_path . '/config/masive.php';
}
if (file_exists($base_path . '/config/salvamont.php')) {
    require_once $base_path . '/config/salvamont.php';
}

// Utils - helpers FIRST (defines base functions)
require_once $base_path . '/utils/helpers.php';

// Services - matrice-service after helpers
if (file_exists($base_path . '/services/matrice-service.php')) {
    require_once $base_path . '/services/matrice-service.php';
}

// Other services
require_once $base_path . '/services/meteo-service.php';
require_once $base_path . '/services/nowcasting.php';
require_once $base_path . '/services/buletin-nivologic.php';
require_once $base_path . '/services/evaluare-factori.php';

// ═══════════════════════════════════════════════════════════════
// INPUT VALIDATION
// ═══════════════════════════════════════════════════════════════

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_GET;
}

$masiv = isset($input['masiv']) ? strtolower(trim($input['masiv'])) : '';
$data = isset($input['data']) ? trim($input['data']) : '';
$nivel_experienta = isset($input['nivel_experienta']) ? strtolower(trim($input['nivel_experienta'])) : 'mediu';
$altitudine_tinta = isset($input['altitudine_tinta']) ? intval($input['altitudine_tinta']) : 1800;

// Validări
$errors = [];

if (empty($masiv)) {
    $errors[] = 'Parametrul "masiv" este obligatoriu';
}

if (empty($data)) {
    $errors[] = 'Parametrul "data" este obligatoriu';
}

if (!in_array($nivel_experienta, ['incepator', 'mediu', 'experimentat'])) {
    $nivel_experienta = 'mediu';
}

if ($altitudine_tinta < 500 || $altitudine_tinta > 3000) {
    $altitudine_tinta = 1800;
}

// Verifică dacă masivul există în configurație
$station_config = getStationConfig($masiv);
if (!$station_config && !empty($masiv)) {
    $errors[] = 'Masiv necunoscut: ' . $masiv;
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'errors' => $errors
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ═══════════════════════════════════════════════════════════════
// MAIN EXECUTION
// ═══════════════════════════════════════════════════════════════

try {
    $today = date('Y-m-d');
    $is_today = ($data === $today);
    $is_future = ($data > $today);
    
    // Informații despre sursele de date
    $source_info = getSourceInfo($masiv, $altitudine_tinta);
    
    // ─────────────────────────────────────────────────────────────
    // 1. FETCH DATE METEO
    // ─────────────────────────────────────────────────────────────
    
    $meteo = [
        'date_curente' => null,
        'date_prognozate' => null,
        'sursa_info' => $source_info
    ];
    
    // Date actuale ANM (întotdeauna pentru context)
    $anm_station = getANMStation($masiv, $altitudine_tinta);
    if ($anm_station) {
        $meteo['date_curente'] = fetchANMData($anm_station, $masiv);
        if ($meteo['date_curente']) {
            $meteo['date_curente']['statie'] = $anm_station;
            $meteo['date_curente']['masurat_la'] = date('c');
        }
    }
    
    // Dacă ANM a eșuat, încearcă direct cu masivul
    if (!$meteo['date_curente']) {
        $meteo['date_curente'] = fetchANMData(null, $masiv);
        if ($meteo['date_curente']) {
            $meteo['date_curente']['masurat_la'] = date('c');
        }
    }
    
    // Prognoză Meteoblue (pentru data selectată)
    if ($is_future || $is_today) {
        $meteoblue_source = getMeteoblueSource($masiv, $altitudine_tinta);
        if ($meteoblue_source) {
            $meteo['date_prognozate'] = fetchMeteoblueData($meteoblue_source, $data, METEOBLUE_API_KEY);
            if ($meteo['date_prognozate']) {
                $meteo['date_prognozate']['predictibilitate'] = calculeazaPredictibilitate($data);
            }
        }
    }
    
    // Alege sursa principală pentru evaluare
    $valori_meteo = [];
    if ($is_today && $meteo['date_curente']) {
        $valori_meteo = $meteo['date_curente'];
        $meteo['sursa_principala'] = 'ANM';
    } elseif ($meteo['date_prognozate']) {
        $valori_meteo = $meteo['date_prognozate'];
        $meteo['sursa_principala'] = 'Meteoblue';
    } elseif ($meteo['date_curente']) {
        $valori_meteo = $meteo['date_curente'];
        $meteo['sursa_principala'] = 'ANM (fallback)';
    }
    
    if (empty($valori_meteo)) {
        // Fallback: generăm valori estimate bazate pe sezon și altitudine
        $luna = intval(date('m', strtotime($data)));
        $is_winter = ($luna >= 11 || $luna <= 3);
        
        $valori_meteo = [
            'temperatura' => $is_winter ? (-5 - ($altitudine_tinta - 1000) * 0.006) : (15 - ($altitudine_tinta - 1000) * 0.006),
            'vant_viteza' => 20,
            'zapada_cm' => $is_winter ? max(0, ($altitudine_tinta - 1200) * 0.1) : 0,
            'vizibilitate' => 'necunoscută',
            'fenomen' => null,
            '_estimat' => true,
            '_nota' => 'Date estimate - verificați condițiile înainte de plecare'
        ];
        
        $meteo['sursa_principala'] = 'Estimare (date indisponibile)';
        $meteo['avertisment'] = 'Nu s-au putut obține date meteo actuale. Valorile sunt estimate.';
    }
    
    // ─────────────────────────────────────────────────────────────
    // 2. FETCH BULETIN NIVOLOGIC (avalanșă + detalii zăpadă)
    // ─────────────────────────────────────────────────────────────
    
    $buletin = getBuletinDataForMasiv($masiv, $altitudine_tinta);
    
    $avalansa = [
        'risc' => 0,
        'nivel_text' => 'Necunoscut',
        'descriere' => null,
        'valabilitate' => null,
        'sursa' => null
    ];
    
    $detalii_zapada_buletin = null;
    
    if ($buletin && $buletin['success']) {
        $avalansa = [
            'risc' => $buletin['risc_avalansa']['nivel'],
            'nivel_text' => $buletin['risc_avalansa']['nivel_text'],
            'descriere' => $buletin['risc_avalansa']['descriere'],
            'valabilitate' => $buletin['valabilitate'],
            'sursa' => 'Buletin Nivologic ANM'
        ];
        
        $detalii_zapada_buletin = $buletin['strat_zapada'];
    }
    
    // ─────────────────────────────────────────────────────────────
    // 3. FETCH COD VREME REA (ANM Nowcasting)
    // ─────────────────────────────────────────────────────────────
    
    $nowcasting = getNowcastingForMasiv($masiv, $altitudine_tinta, $data);
    
    $cod_vreme_rea = [
        'activ' => false,
        'cod' => null,
        'cod_display' => null,
        'valabilitate' => null,
        'fenomene' => [],
        'mesaj' => null
    ];
    
    if ($nowcasting && $nowcasting['success'] && $nowcasting['cod_activ']) {
        $cod_vreme_rea = [
            'activ' => true,
            'cod' => $nowcasting['cod_activ'],
            'cod_display' => $nowcasting['cod_display'],
            'valabilitate' => $nowcasting['valabilitate'],
            'fenomene' => $nowcasting['fenomene'] ?? [],
            'mesaj' => $nowcasting['mesaj']
        ];
    }
    
    // ─────────────────────────────────────────────────────────────
    // 4. DETERMINARE CONTEXT (sezon, dificultate)
    // ─────────────────────────────────────────────────────────────
    
    $context = determinaContext($masiv, $data, $altitudine_tinta);
    
    // ─────────────────────────────────────────────────────────────
    // 5. EVALUARE FACTORI METEO
    // ─────────────────────────────────────────────────────────────
    
    $evaluare = evaluareFactoriComplet(
        $valori_meteo,
        $avalansa,
        $cod_vreme_rea,
        $detalii_zapada_buletin,
        $meteo['date_curente'],
        $context
    );
     */

    // ─────────────────────────────────────────────────────────────
    // 5.5. ANALIZĂ CONTEXT DINAMIC (pentru context-card)
    // ─────────────────────────────────────────────────────────────
    
    $context_dinamic = analizaContextDinamic(
        $evaluare['factori'],
        $evaluare['meteo_status'],
        $nivel_experienta,
        $altitudine_tinta
    );
    
    // Merge context static + dinamic
    $context['conditii_text'] = $context_dinamic['conditii_text'];
    $context['recomandari'] = $context_dinamic['recomandari'];
    $context['factori_critici_count'] = $context_dinamic['factori_critici_count'];
    $context['factori_atentie_count'] = $context_dinamic['factori_atentie_count'];
    $context['factori_severi_count'] = $context_dinamic['factori_severi_count'];

    // ─────────────────────────────────────────────────────────────
    // 6. APLICARE MATRICE RISC → DECIZIE FINALĂ
    // ─────────────────────────────────────────────────────────────
    
    $decizie = aplicaMatrice(
        $nivel_experienta,
        $context['sezon'],
        $context['dificultate_grad'],
        $evaluare['meteo_status'],
        $context
    );
    
    // ─────────────────────────────────────────────────────────────
    // 7. ECHIPAMENT RECOMANDAT
    // ─────────────────────────────────────────────────────────────
    
    $echipament = genereazaEchipament(
        $decizie['status'],
        $context['sezon'],
        $valori_meteo['temperatura'] ?? 0,
        $valori_meteo['zapada_cm'] ?? 0,
        $cod_vreme_rea
    );
    
    // ─────────────────────────────────────────────────────────────
    // 8. CONTACT SALVAMONT
    // ─────────────────────────────────────────────────────────────
    
    $salvamont = getContactSalvamont($masiv);
    
    // ─────────────────────────────────────────────────────────────
    // 9. CONSTRUIRE RĂSPUNS
    // ─────────────────────────────────────────────────────────────
    
    $response = [
        'success' => true,
        'timestamp' => date('c'),
        'masiv' => ucfirst(str_replace('-', ' ', $masiv)),
        'data' => $data,
        
        'input' => [
            'nivel_experienta' => $nivel_experienta,
            'altitudine_tinta' => $altitudine_tinta
        ],
        
        'meteo' => $meteo,
        
        'context' => $context,
        
        'evaluare' => [
            'factori' => $evaluare['factori'],
            'no_go_count' => $evaluare['no_go_count'],
            'caution_count' => $evaluare['caution_count'],
            'factori_severi_count' => $evaluare['factori_severi_count'] ?? 0, // ← NOU
            'meteo_status' => $evaluare['meteo_status']
        ],

        
        'cod_vreme_rea' => $cod_vreme_rea,
        
        'decizie' => $decizie,
        
        'echipament' => $echipament,
        
        'salvamont' => $salvamont
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

// ═══════════════════════════════════════════════════════════════
// FUNCȚII HELPER LOCALE
// ═══════════════════════════════════════════════════════════════

/**
 * Calculează predictibilitatea prognozei bazat pe numărul de zile
 */
function calculeazaPredictibilitate($data_tinta) {
    $today = new DateTime();
    $target = new DateTime($data_tinta);
    $diff = $today->diff($target)->days;
    
    if ($diff <= 1) {
        return ['nivel' => 'ridicata', 'procent' => 90, 'text' => 'Predictibilitate ridicată'];
    } elseif ($diff <= 3) {
        return ['nivel' => 'buna', 'procent' => 75, 'text' => 'Predictibilitate bună'];
    } elseif ($diff <= 5) {
        return ['nivel' => 'moderata', 'procent' => 60, 'text' => 'Predictibilitate moderată'];
    } elseif ($diff <= 7) {
        return ['nivel' => 'scazuta', 'procent' => 45, 'text' => 'Predictibilitate scăzută'];
    } else {
        return ['nivel' => 'foarte_scazuta', 'procent' => 30, 'text' => 'Predictibilitate foarte scăzută - verifică din nou înainte de plecare'];
    }
}
