<?php
/**
 * Serviciu risc avalanșă
 */

require_once __DIR__ . '/../utils/helpers.php';

/**
 * Preia risc avalanșă pentru masiv
 */
function getRiscAvalansa($masiv_slug) {
    $url = 'https://merglamunte.ro/api/avalansa.php?masiv=' . urlencode($masiv_slug);
    
    $result = fetchAPI($url);
    
    if (!$result['success']) {
        // Nu avem date avalanșă - returnăm risc necunoscut
        return [
            'disponibil' => false,
            'risc' => 0,
            'nota' => 'Date avalanșă indisponibile'
        ];
    }
    
    $data = json_decode($result['data'], true);
    
    if (!$data || !$data['success']) {
        return [
            'disponibil' => false,
            'risc' => 0,
            'nota' => 'Date avalanșă invalide'
        ];
    }
    
    $risc = intval($data['risc_avalansa'] ?? 0);
    
    // Extrage validitate din buletin (dacă există)
    $validitate = extrageValiditate($data);
    
    return [
        'disponibil' => true,
        'risc' => $risc,
        'validitate' => $validitate,
        'sursa' => 'ANM - Buletin avalanșe'
    ];
}

/**
 * Extrage perioada de validitate din datele avalanșă
 */
function extrageValiditate($data_avalansa) {
    // Încercăm să extragem din câmpuri cunoscute
    if (isset($data_avalansa['validitate_de_la']) && isset($data_avalansa['validitate_pana_la'])) {
        return [
            'de_la' => $data_avalansa['validitate_de_la'],
            'pana_la' => $data_avalansa['validitate_pana_la']
        ];
    }
    
    // Altfel presupunem valabil pentru azi și mâine
    return [
        'de_la' => date('Y-m-d') . 'T00:00:00Z',
        'pana_la' => date('Y-m-d', strtotime('+1 day')) . 'T23:59:59Z'
    ];
}

/**
 * Verifică dacă buletinul avalanșă este valid pentru data cerută
 */
function esteValidPentruData($validitate, $data_ceruta) {
    $data_de_la = strtotime($validitate['de_la']);
    $data_pana_la = strtotime($validitate['pana_la']);
    $data_target = strtotime($data_ceruta);
    
    return $data_target >= $data_de_la && $data_target <= $data_pana_la;
}

/**
 * Generează avertisment dacă buletinul e vechi pentru data cerută
 */
function genereazaAvertismentAvalansa($validitate, $data_ceruta) {
    if (!esteValidPentruData($validitate, $data_ceruta)) {
        $data_valid = date('d.m.Y', strtotime($validitate['pana_la']));
        $data_cerita = date('d.m.Y', strtotime($data_ceruta));
        
        return "⚠️ Buletin avalanșe disponibil doar până la $data_valid. Pentru $data_cerita, verifică buletin actualizat în ziua respectivă.";
    }
    
    return null;
}
