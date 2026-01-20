<?php
/**
 * Serviciu Buletin Nivologic - folosește date_meteo.json (structurat)
 * Sursa: https://raw.githubusercontent.com/SIG212/meteo-scraper/main/date_meteo.json
 */

/**
 * Mapping masive -> chei din JSON
 * null = indisponibil, 'parang' pentru Retezat = copiază din Parâng
 */
function getMasivToJsonKeyMapping() {
    return [
        // Masive cu date directe în JSON
        'rodnei' => 'rodnei',
        'bistritei' => 'bistritei',
        'calimani' => 'calimani',
        'ceahlau' => 'ceahlau',
        'fagaras' => 'fagaras',
        'bucegi' => 'bucegi',
        'parang' => 'parang',
        'sureanu' => 'sureanu',
        'tarcu' => 'tarcu',
        'godeanu' => 'godeanu',
        'vladeasa' => 'vladeasa',
        'apuseni' => 'occidentali',
        'occidentali' => 'occidentali',
        
        // Retezat -> copiază din Parâng (cazul special)
        'retezat' => 'parang',
        
        // Masive fără date -> vor afișa "indisponibil"
        'piatra-craiului' => 'fagaras',
        'cindrel' => null,
        'cozia' => null,
        'iezer' => null,
        'baiului' => null,
        'ciucas' => null,
        'hasmas' => null,
        'maramuresului' => 'rodnei;',
        'gutai' => 'orientali',
        'tibles' => 'orientali'
    ];
}

/**
 * Descarcă JSON-ul de pe GitHub
 */
function fetchBuletinJson() {
    $json_url = 'https://raw.githubusercontent.com/SIG212/meteo-scraper/main/date_meteo.json';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $json_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200 || !$response) {
        return ['success' => false, 'error' => 'Eroare la descărcarea buletinului (HTTP ' . $http_code . ')'];
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['success' => false, 'error' => 'JSON invalid: ' . json_last_error_msg()];
    }
    
    if (!isset($data['date']) || empty($data['date'])) {
        return ['success' => false, 'error' => 'JSON nu conține date valide.'];
    }
    
    return ['success' => true, 'data' => $data];
}

/**
 * Determină categoria de altitudine (peste_1800 sau sub_1800)
 */
function getAltitudeCategory($altitudine_tinta) {
    return $altitudine_tinta >= 1800 ? 'peste_1800' : 'sub_1800';
}

/**
 * Extrage data de valabilitate din JSON
 */
function extractValabilitateFromJson($json_data) {
    // Dacă JSON-ul conține informații de valabilitate directe
    if (isset($json_data['valabilitate'])) {
        return $json_data['valabilitate'];
    }
    
    // Folosește ultima_actualizare ca referință
    if (isset($json_data['ultima_actualizare'])) {
        try {
            $date = new DateTime($json_data['ultima_actualizare']);
            $de_la = $date->format('Y-m-d');
            
            $date->modify('+1 day');
            $pana_la = $date->format('Y-m-d');
            
            return [
                'de_la' => $de_la,
                'pana_la' => $pana_la
            ];
        } catch (Exception $e) {
            return null;
        }
    }
    
    return null;
}

/**
 * Generează descriere implicită bazată pe nivel
 */
function generateDefaultDescriere($nivel) {
    $descrieri = [
        0 => 'Date despre riscul de avalanșă indisponibile pentru acest masiv.',
        1 => 'Risc scăzut. Avalanșe mici posibile doar pe pante foarte abrupte.',
        2 => 'Risc moderat. Avalanșe posibile pe pante abrupte, mai ales la suprasarcini.',
        3 => 'Risc însemnat. Avalanșe medii și mari posibile. Evitați pantele abrupte.',
        4 => 'Risc ridicat. Avalanșe mari probabile, declanșare spontană frecventă.',
        5 => 'Risc foarte ridicat. Activitate avalanșoasă masivă. NU mergeți la munte!'
    ];
    
    return $descrieri[$nivel] ?? 'Consultați buletinul ANM pentru detalii.';
}

/**
 * Funcția principală - INTERFAȚĂ PENTRU analiza-v3.php
 */
function getBuletinDataForMasiv($masiv, $altitudine_tinta = 2000) {
    $fetch_result = fetchBuletinJson();
    
    if (!$fetch_result['success']) {
        return [
            'success' => false,
            'error' => $fetch_result['error'],
            'risc_avalansa' => [
                'nivel' => 0,
                'nivel_text' => 'Necunoscut',
                'descriere' => 'Nu s-a putut descărca buletinul nivologic.'
            ],
            'valabilitate' => null,
            'strat_zapada' => null
        ];
    }
    
    $json_data = $fetch_result['data'];
    $masiv_mapping = getMasivToJsonKeyMapping();
    $masiv_key = strtolower($masiv);
    
    // Verifică dacă masivul este mapat
    if (!array_key_exists($masiv_key, $masiv_mapping)) {
        // Masiv complet necunoscut
        return [
            'success' => false,
            'error' => 'Masivul "' . $masiv . '" nu este recunoscut în sistem.',
            'risc_avalansa' => [
                'nivel' => 0,
                'nivel_text' => 'Necunoscut',
                'descriere' => 'Masiv necunoscut.'
            ],
            'valabilitate' => null,
            'strat_zapada' => null
        ];
    }
    
    $json_key = $masiv_mapping[$masiv_key];
    
    // Cazul special: masiv fără date disponibile (null)
   // Cazul special: masiv fără date disponibile (null)
if ($json_key === null) {
    return [
        'success' => true,
        'risc_avalansa' => [
            'nivel' => 0,
            'nivel_text' => 'Indisponibil',  // ← SCHIMBAT din 'Necunoscut'
            'descriere' => 'Risc avalanșă indisponibil pentru acest masiv.'
        ],
        'valabilitate' => extractValabilitateFromJson($json_data),
        'strat_zapada' => null,
        'sursa' => 'Buletin Nivologic ANM (via GitHub scraper - JSON)',
        'ultima_actualizare' => $json_data['ultima_actualizare'] ?? null
    ];
}
    
    // Verifică dacă există date pentru acest masiv în JSON
    if (!isset($json_data['date'][$json_key])) {
        return [
            'success' => true,
            'risc_avalansa' => [
                'nivel' => 0,
                'nivel_text' => 'Indisponibil',
                'descriere' => 'Risc avalanșă indisponibil pentru acest masiv.'
            ],
            'valabilitate' => extractValabilitateFromJson($json_data),
            'strat_zapada' => null,
            'sursa' => 'Buletin Nivologic ANM (via GitHub scraper - JSON)',
            'ultima_actualizare' => $json_data['ultima_actualizare'] ?? null
        ];
    }
    
    $masiv_data = $json_data['date'][$json_key];
    $altitude_category = getAltitudeCategory($altitudine_tinta);
    
    // Verifică dacă există date pentru categoria de altitudine
    if (!isset($masiv_data[$altitude_category])) {
        return [
            'success' => true,
            'risc_avalansa' => [
                'nivel' => 0,
                'nivel_text' => 'Indisponibil',
                'descriere' => 'Date indisponibile pentru altitudinea ' . $altitudine_tinta . 'm.'
            ],
            'valabilitate' => extractValabilitateFromJson($json_data),
            'strat_zapada' => null,
            'sursa' => 'Buletin Nivologic ANM (via GitHub scraper - JSON)',
            'ultima_actualizare' => $json_data['ultima_actualizare'] ?? null
        ];
    }
    
    $risc_data = $masiv_data[$altitude_category];
    
    // Extrage date
    $nivel = $risc_data['nivel'] ?? 0;
    $nivel_text = $risc_data['text'] ?? 'Necunoscut';
    
    // Generează descriere implicită (JSON-ul nu mai conține descriere)
    $descriere = generateDefaultDescriere($nivel);
    
    // STRUCTURĂ COMPATIBILĂ CU analiza-v3.php
    return [
        'success' => true,
        'risc_avalansa' => [
            'nivel' => $nivel,
            'nivel_text' => $nivel_text,
            'descriere' => $descriere
        ],
        'valabilitate' => extractValabilitateFromJson($json_data),
        'strat_zapada' => null, // JSON-ul nu mai conține detalii despre strat
        'sursa' => 'Buletin Nivologic ANM (via GitHub scraper - JSON)',
        'ultima_actualizare' => $json_data['ultima_actualizare'] ?? null,
        'altitudine_categorie' => $altitude_category
    ];
}