<?php
/**
 * Serviciu Meteo - Fetch date direct de la ANM și Meteoblue
 */

/**
 * Fetch date de la stațiile ANM (API oficial)
 */
function fetchANMData($statie_nume, $masiv = null) {
    // API-ul oficial ANM
    $url = 'https://www.meteoromania.ro/wp-json/meteoapi/v2/starea-vremii';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MergLaMunte/1.0)');
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200 || !$response) {
        error_log("ANM API fetch failed: HTTP $http_code");
        return null;
    }
    
    $data = json_decode($response, true);
    
    if (!$data || !isset($data['features'])) {
        error_log("ANM API parse failed");
        return null;
    }
    
    // Caută stația dorită
    $statie_data = null;
    $statie_nume_upper = strtoupper($statie_nume);
    
    foreach ($data['features'] as $feature) {
        $nume = strtoupper($feature['properties']['nume'] ?? '');
        if ($nume === $statie_nume_upper || strpos($nume, $statie_nume_upper) !== false) {
            $statie_data = $feature['properties'];
            break;
        }
    }
    
    if (!$statie_data) {
        error_log("ANM station not found: $statie_nume");
        return null;
    }
    
    return [
        'temperatura' => parseFloat($statie_data['tempe'] ?? null),
        'vant_viteza' => parseVant($statie_data['vant'] ?? null),
        'zapada_cm' => parseZapada($statie_data['zapada'] ?? null),
        'vizibilitate' => parseVizibilitate($statie_data['nebulozitate'] ?? null, $statie_data['fenomen_e'] ?? null),
        'umiditate' => isset($statie_data['umezeala']) ? floatval($statie_data['umezeala']) : null,
        'fenomen' => $statie_data['fenomen_e'] ?? null,
        'nebulozitate' => $statie_data['nebulozitate'] ?? null,
        'presiune' => parsePresiune($statie_data['presiunetext'] ?? null),
        'statie_nume' => $statie_data['nume'] ?? $statie_nume,
        'actualizat' => str_replace('&nbsp;', ' ', $statie_data['actualizat'] ?? '')
    ];
}

/**
 * Parsează vizibilitatea din nebulozitate și fenomen
 */
function parseVizibilitate($nebulozitate, $fenomen) {
    if (!$nebulozitate && !$fenomen) return null;
    
    $text = strtolower(($nebulozitate ?? '') . ' ' . ($fenomen ?? ''));
    
    if (strpos($text, 'invizibil') !== false || strpos($text, 'ceata') !== false) {
        return 'foarte_redusa';
    }
    if (strpos($text, 'cetos') !== false) {
        return 'redusa';
    }
    if (strpos($text, 'acoperit') !== false) {
        return 'moderata';
    }
    if (strpos($text, 'senin') !== false || strpos($text, 'partial') !== false) {
        return 'buna';
    }
    
    return null;
}

/**
 * Parsează presiunea
 */
function parsePresiune($text) {
    if (!$text) return null;
    if (preg_match('/([\d.]+)\s*mb/i', $text, $matches)) {
        return floatval($matches[1]);
    }
    return null;
}

/**
 * Fetch date de la Meteoblue API
 */
function fetchMeteoblueData($source, $data_tinta, $api_key) {
    // Construiește URL-ul
    $lat = null;
    $lon = null;
    
    if (is_array($source)) {
        $lat = $source['lat'];
        $lon = $source['lon'];
    } else {
        // Extrage coordonatele din location string sau folosește mapping
        $coords = getMeteoblueCoordinates($source);
        if ($coords) {
            $lat = $coords['lat'];
            $lon = $coords['lon'];
        }
    }
    
    if (!$lat || !$lon) {
        return null;
    }
    
    // API Meteoblue - basic-day package
    $url = sprintf(
        'https://my.meteoblue.com/packages/basic-day?lat=%s&lon=%s&apikey=%s&format=json&forecast_days=7',
        $lat,
        $lon,
        $api_key
    );
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200 || !$response) {
        error_log("Meteoblue fetch failed: HTTP $http_code, URL: $url");
        return null;
    }
    
    $data = json_decode($response, true);
    
    if (!$data || isset($data['error'])) {
        error_log("Meteoblue parse error: " . ($data['error'] ?? 'unknown'));
        return null;
    }
    
    // Găsește indexul pentru data țintă
    $target_index = 0;
    if (isset($data['data_day']['time'])) {
        foreach ($data['data_day']['time'] as $i => $date) {
            if ($date === $data_tinta) {
                $target_index = $i;
                break;
            }
        }
    }
    
    // Extrage valorile pentru ziua țintă
    $day_data = $data['data_day'] ?? [];
    
    $temp_max = $day_data['temperature_max'][$target_index] ?? null;
    $temp_min = $day_data['temperature_min'][$target_index] ?? null;
    $temp_medie = ($temp_max !== null && $temp_min !== null) ? ($temp_max + $temp_min) / 2 : null;
    
    $result = [
        'temperatura' => $temp_medie,
        'temperatura_max' => $temp_max,
        'temperatura_min' => $temp_min,
        'temperatura_resimtita' => $day_data['felttemperature_max'][$target_index] ?? $temp_max,
        'vant_viteza' => $day_data['windspeed_mean'][$target_index] ?? null,
        'vant_rafale' => $day_data['windspeed_max'][$target_index] ?? null,
        'precipitatii' => $day_data['precipitation'][$target_index] ?? 0,
        'precipitatii_probabilitate' => $day_data['precipitation_probability'][$target_index] ?? 0,
        'pictocode' => $day_data['pictocode'][$target_index] ?? null,
        'uv_index' => $day_data['uvindex'][$target_index] ?? null
    ];
    
    // Determină tipul precipitațiilor
    $result['tip_precipitatii'] = determinaTipPrecipitatii(
        $result['precipitatii'],
        $temp_medie,
        $result['pictocode']
    );
    
    return $result;
}

/**
 * Coordonate pentru locațiile Meteoblue cunoscute
 */
function getMeteoblueCoordinates($location_string) {
    // Mapping pentru locațiile din config
    $coordinates = [
        'bucegi-mountains_romania_683598' => ['lat' => 45.400, 'lon' => 25.450],
        'făgăraș-mountains_romania_678498' => ['lat' => 45.600, 'lon' => 24.600],
        'vf.-retezat_romania_11810090' => ['lat' => 45.350, 'lon' => 22.880],
        'piatra-craiului-mountains_romania_670901' => ['lat' => 45.520, 'lon' => 25.230],
        'parâng-mountains_romania_671333' => ['lat' => 45.350, 'lon' => 23.550],
        'muntele-Ţarcul_romania_665512' => ['lat' => 45.280, 'lon' => 22.520],
        'munţii-iezer_romania_675717' => ['lat' => 45.430, 'lon' => 25.100],
        'munţii-cindrel_romania_681803' => ['lat' => 45.620, 'lon' => 23.900],
        'cozia_romania_8260398' => ['lat' => 45.300, 'lon' => 24.340],
        'buila_romania_8410689' => ['lat' => 45.240, 'lon' => 24.130],
        'baiu-mountains_romania_685760' => ['lat' => 45.410, 'lon' => 25.670],
        'pietrosul-rodnei_romania_8741063' => ['lat' => 47.590, 'lon' => 24.630],
        'masivul-ceahlău_romania_682481' => ['lat' => 46.950, 'lon' => 25.940],
        'pietrosul-călimanilor_romania_670813' => ['lat' => 47.100, 'lon' => 25.240],
        'munţii-hăşmaş_romania_676284' => ['lat' => 46.680, 'lon' => 25.820],
        'giumalău_romania_677132' => ['lat' => 47.450, 'lon' => 25.480],
        'hora-pip-ivan_romania_506555' => ['lat' => 47.930, 'lon' => 24.550],
        'apuseni-mountains_romania_686257' => ['lat' => 46.550, 'lon' => 22.750],
        'muntii-mehedinţi_romania_673611' => ['lat' => 45.050, 'lon' => 22.680],
        'ciucas_romania_7874266' => ['lat' => 45.530, 'lon' => 25.930]
    ];
    
    // Caută exact
    if (isset($coordinates[$location_string])) {
        return $coordinates[$location_string];
    }
    
    // Caută parțial
    foreach ($coordinates as $key => $coords) {
        if (stripos($location_string, explode('_', $key)[0]) !== false) {
            return $coords;
        }
    }
    
    // Încearcă să extragă coordonatele dacă sunt în format lat/lon
    if (preg_match('/(\d+\.\d+)\s+(\d+\.\d+)/', $location_string, $matches)) {
        return ['lat' => floatval($matches[1]), 'lon' => floatval($matches[2])];
    }
    
    return null;
}

/**
 * Determină tipul precipitațiilor
 */
function determinaTipPrecipitatii($cantitate, $temperatura, $pictocode = null) {
    if ($cantitate < 0.1) {
        return 'fara';
    }
    
    // Pictocodes Meteoblue pentru ninsoare: 14, 15, 16, 22, 23, 24
    $snow_codes = [14, 15, 16, 22, 23, 24];
    
    if ($pictocode && in_array($pictocode, $snow_codes)) {
        return 'ninsoare';
    }
    
    if ($temperatura !== null) {
        if ($temperatura < -2) {
            return 'ninsoare';
        } elseif ($temperatura >= -2 && $temperatura <= 2) {
            return 'lapovita';
        }
    }
    
    return 'ploaie';
}

// ═══════════════════════════════════════════════════════════════
// FUNCȚII HELPER PARSARE
// ═══════════════════════════════════════════════════════════════

if (!function_exists('parseFloat')) {
    function parseFloat($value) {
        if ($value === null || $value === '') return null;
        $clean = preg_replace('/[^0-9.\-]/', '', str_replace(',', '.', $value));
        return is_numeric($clean) ? floatval($clean) : null;
    }
}

if (!function_exists('parseVant')) {
    function parseVant($vant_raw) {
        if (!$vant_raw) return null;
        
        if (preg_match('/([0-9.]+)\s*m\/s/i', $vant_raw, $matches)) {
            return floatval($matches[1]) * 3.6;
        }
        
        if (preg_match('/([0-9.]+)\s*km\/h/i', $vant_raw, $matches)) {
            return floatval($matches[1]);
        }
        
        if (is_numeric($vant_raw)) {
            return floatval($vant_raw);
        }
        
        return null;
    }
}

if (!function_exists('parseZapada')) {
    function parseZapada($zapada_raw) {
        if (!$zapada_raw) return 0;
        
        if (preg_match('/([0-9]+)\s*cm/i', $zapada_raw, $matches)) {
            return intval($matches[1]);
        }
        
        if (is_numeric($zapada_raw)) {
            return intval($zapada_raw);
        }
        
        return 0;
    }
}