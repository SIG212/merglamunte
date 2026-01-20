<?php
/**
 * Helper-uri pentru lucru cu date
 */

if (!function_exists('validDate')) {
    /**
     * Verifică dacă data este validă (format YYYY-MM-DD)
     */
    function validDate($date_string) {
        $d = DateTime::createFromFormat('Y-m-d', $date_string);
        return $d && $d->format('Y-m-d') === $date_string;
    }
}

if (!function_exists('diferentaZile')) {
    /**
     * Calculează diferența în zile între două date
     */
    function diferentaZile($data1, $data2 = null) {
        if ($data2 === null) {
            $data2 = date('Y-m-d');
        }
        
        $diff = (strtotime($data1) - strtotime($data2)) / 86400;
        return round($diff);
    }
}

if (!function_exists('esteTrecut')) {
    /**
     * Verifică dacă data este în trecut (ÎNAINTE de azi)
     */
    function esteTrecut($data) {
        $today = strtotime(date('Y-m-d'));
        $data_ts = strtotime($data);
        return $data_ts < $today; // Azi NU e trecut
    }
}

if (!function_exists('esteAzi')) {
    /**
     * Verifică dacă data este azi
     */
    function esteAzi($data) {
        return $data === date('Y-m-d');
    }
}

if (!function_exists('esteViitor')) {
    /**
     * Verifică dacă data este în viitor (1-10 zile)
     */
    function esteViitor($data, $max_zile = 10) {
        $diff = diferentaZile($data);
        return $diff > 0 && $diff <= $max_zile;
    }
}

if (!function_exists('formateazaTimestamp')) {
    /**
     * Formatează timestamp pentru API (ISO 8601)
     */
    function formateazaTimestamp($timestamp = null) {
        if ($timestamp === null) {
            $timestamp = time();
        }
        
        return date('c', $timestamp);
    }
}

if (!function_exists('determinareSezon')) {
    /**
     * Determină sezonul pe baza datei și zăpezii
     */
    function determinareSezon($data, $zapada_cm = 0) {
        $luna = intval(date('m', strtotime($data)));
        
        // Iarnă: noiembrie - martie
        if ($luna >= 11 || $luna <= 3) {
            return 'iarna';
        }
        
        // Primăvară/Toamnă cu zăpadă multă → considerăm iarnă
        if (($luna == 4 || $luna == 10) && $zapada_cm > 50) {
            return 'iarna';
        }
        
        return 'vara';
    }
}

if (!function_exists('numeLuna')) {
    /**
     * Returnează numele lunii în română
     */
    function numeLuna($luna_numar) {
        $luni = [
            1 => 'ianuarie', 2 => 'februarie', 3 => 'martie',
            4 => 'aprilie', 5 => 'mai', 6 => 'iunie',
            7 => 'iulie', 8 => 'august', 9 => 'septembrie',
            10 => 'octombrie', 11 => 'noiembrie', 12 => 'decembrie'
        ];
        
        return $luni[$luna_numar] ?? '';
    }
}
