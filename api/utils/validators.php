<?php
/**
 * Validare input API
 */

require_once __DIR__ . '/date-helpers.php';

/**
 * Validează toate input-urile API
 * Returnează array cu errori (gol dacă totul e valid)
 */
function validateInput($input) {
    $errors = [];
    
    // Validare masiv
    if (empty($input['masiv'])) {
        $errors[] = 'Parametrul "masiv" este obligatoriu';
    } elseif (!masivValid($input['masiv'])) {
        $errors[] = 'Masiv invalid. Masive disponibile: bucegi, fagaras, retezat, piatra-craiului, etc.';
    }
    
    // Validare dată
    if (empty($input['data'])) {
        $errors[] = 'Parametrul "data" este obligatoriu';
    } elseif (!validDate($input['data'])) {
        $errors[] = 'Format dată invalid. Folosiți formatul YYYY-MM-DD (ex: 2026-01-15)';
    } else {
        // Verificare interval valid
        if (esteTrecut($input['data'])) {
            $errors[] = 'Nu putem evalua date din trecut';
        }
        
        $diff_zile = diferentaZile($input['data']);
        if ($diff_zile > 7) {
            $errors[] = 'Prognoză meteo disponibilă doar pentru următoarele 7 zile. Pentru date mai îndepărtate, verifică din nou când se apropie data drumeției.';
        }
    }
    
    // Validare nivel experiență
    $nivele_valide = ['incepator', 'mediu', 'experimentat'];
    if (empty($input['nivel_experienta'])) {
        $errors[] = 'Parametrul "nivel_experienta" este obligatoriu';
    } elseif (!in_array($input['nivel_experienta'], $nivele_valide)) {
        $errors[] = 'Nivel experiență invalid. Valori acceptate: incepator, mediu, experimentat';
    }
    
    // Validare altitudine
    if (empty($input['altitudine_tinta'])) {
        $errors[] = 'Parametrul "altitudine_tinta" este obligatoriu';
    } elseif (!is_numeric($input['altitudine_tinta'])) {
        $errors[] = 'Altitudinea trebuie să fie un număr';
    } else {
        $alt = intval($input['altitudine_tinta']);
        if ($alt < 500 || $alt > 2700) {
            $errors[] = 'Altitudine invalidă. Interval acceptat: 500-2700 metri';
        }
    }
    
    return $errors;
}

/**
 * Verifică dacă masivul există în configurație
 */
function masivValid($masiv_slug) {
    $masive = require __DIR__ . '/../config/masive.php';
    return isset($masive[$masiv_slug]);
}

/**
 * Sanitizare input string
 */
function sanitizeString($str) {
    return trim(strip_tags($str));
}

/**
 * Sanitizare input număr
 */
function sanitizeNumber($num) {
    return filter_var($num, FILTER_SANITIZE_NUMBER_INT);
}
