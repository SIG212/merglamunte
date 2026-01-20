<?php
/**
 * Serviciu ANM Nowcasting - Avertizări cod galben/portocaliu/roșu
 * Sursa: https://www.meteoromania.ro/avertizari-nowcasting/
 * 
 * Parsează textul avertizărilor și identifică dacă masivul selectat e afectat
 */

/**
 * Mapping masive -> zone geografice și cuvinte cheie pentru matching
 */
function getZoneMapping() {
    return [
        // Carpații Meridionali
        'bucegi' => ['carpații meridionali', 'meridionali', 'bucegi', 'prahova', 'dâmbovița', 'brașov'],
        'fagaras' => ['carpații meridionali', 'meridionali', 'făgăraș', 'sibiu', 'argeș', 'zona înaltă'],
        'piatra-craiului' => ['carpații meridionali', 'meridionali', 'piatra craiului', 'brașov', 'argeș'],
        'retezat' => ['carpații meridionali', 'meridionali', 'retezat', 'hunedoara'],
        'parang' => ['carpații meridionali', 'meridionali', 'parâng', 'gorj', 'hunedoara', 'vâlcea'],
        'tarcu' => ['carpații meridionali', 'meridionali', 'țarcu', 'caraș-severin'],
        'godeanu' => ['carpații meridionali', 'meridionali', 'godeanu', 'caraș-severin', 'mehedinți'],
        'cindrel' => ['carpații meridionali', 'meridionali', 'cindrel', 'sibiu'],
        'cozia' => ['carpații meridionali', 'meridionali', 'cozia', 'vâlcea'],
        'iezer' => ['carpații meridionali', 'meridionali', 'iezer', 'păpușa', 'argeș', 'dâmbovița'],
        'baiului' => ['carpații meridionali', 'meridionali', 'baiului', 'prahova', 'brașov'],
        'ciucas' => ['carpații meridionali', 'meridionali', 'ciucaș', 'prahova', 'brașov', 'buzău'],
        'buila' => ['carpații meridionali', 'meridionali', 'buila', 'vânturariţa', 'vâlcea'],
        
        // Carpații Orientali
        'rodnei' => ['carpații orientali', 'orientali', 'rodnei', 'maramureș', 'bistrița-năsăud'],
        'ceahlau' => ['carpații orientali', 'orientali', 'ceahlău', 'neamț'],
        'calimani' => ['carpații orientali', 'orientali', 'călimani', 'suceava', 'bistrița-năsăud', 'mureș'],
        'hasmas' => ['carpații orientali', 'orientali', 'hășmaș', 'harghita'],
        'bistritei' => ['carpații orientali', 'orientali', 'bistriței', 'neamț', 'suceava'],
        'maramuresului' => ['carpații orientali', 'orientali', 'maramureș', 'maramureșului'],
        
        // Carpații Occidentali
        'apuseni' => ['carpații occidentali', 'occidentali', 'apuseni', 'bihor', 'alba', 'cluj', 'arad'],
        'mehedinti-cernei' => ['carpații occidentali', 'occidentali', 'mehedinți', 'cernei', 'caraș-severin']
    ];
}

/**
 * Extrage avertizările de pe pagina ANM Nowcasting
 */
function fetchNowcastingWarnings() {
    $url = 'https://www.meteoromania.ro/avertizari-nowcasting/';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MergLaMunte/1.0)');
    
    $html = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200 || !$html) {
        return [
            'success' => false,
            'error' => 'Nu s-a putut accesa pagina ANM Nowcasting'
        ];
    }
    
    return [
        'success' => true,
        'html' => $html
    ];
}

/**
 * Parsează HTML-ul pentru a extrage avertizările
 */
function parseWarnings($html) {
    $warnings = [];
    
    // Convertește encoding dacă e necesar
    $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
    
    // Pattern-uri pentru coduri de avertizare
    $cod_patterns = [
        'rosu' => '/cod\s+roșu|avertizare\s+roșie|codul\s+roșu/iu',
        'portocaliu' => '/cod\s+portocaliu|avertizare\s+portocalie|codul\s+portocaliu/iu',
        'galben' => '/cod\s+galben|avertizare\s+galbenă|codul\s+galben/iu'
    ];
    
    // Caută secțiunile de avertizare
    // ANM folosește div-uri sau paragrafe pentru fiecare avertizare
    
    // Metodă 1: Caută în div-uri de avertizare
    if (preg_match_all('/<div[^>]*class="[^"]*avertizare[^"]*"[^>]*>(.*?)<\/div>/is', $html, $matches)) {
        foreach ($matches[1] as $content) {
            $warning = parseWarningContent(strip_tags($content));
            if ($warning) {
                $warnings[] = $warning;
            }
        }
    }
    
    // Metodă 2: Caută în article sau secțiuni
    if (preg_match_all('/<article[^>]*>(.*?)<\/article>/is', $html, $matches)) {
        foreach ($matches[1] as $content) {
            $warning = parseWarningContent(strip_tags($content));
            if ($warning) {
                $warnings[] = $warning;
            }
        }
    }
    
    // Metodă 3: Caută direct în tot textul pentru pattern-uri de avertizare
    $full_text = strip_tags($html);
    $full_text = preg_replace('/\s+/', ' ', $full_text);
    
    // Caută blocuri de text care conțin coduri
    foreach ($cod_patterns as $nivel => $pattern) {
        if (preg_match_all('/[^.]*' . substr($pattern, 1, -3) . '[^.]*\./iu', $full_text, $matches)) {
            foreach ($matches[0] as $sentence) {
                $warning = parseWarningContent($sentence, $nivel);
                if ($warning && !isDuplicateWarning($warnings, $warning)) {
                    $warnings[] = $warning;
                }
            }
        }
    }
    
    return $warnings;
}

/**
 * Verifică dacă avertizarea e duplicat
 */
function isDuplicateWarning($warnings, $new_warning) {
    foreach ($warnings as $existing) {
        if ($existing['cod'] === $new_warning['cod'] && 
            similar_text($existing['text'], $new_warning['text']) > 80) {
            return true;
        }
    }
    return false;
}

/**
 * Parsează conținutul unei avertizări
 */
function parseWarningContent($text, $known_level = null) {
    $text = trim($text);
    if (strlen($text) < 20) return null;
    
    // Determină nivelul codului
    $cod = $known_level;
    if (!$cod) {
        if (preg_match('/cod\s+roșu|roșie/iu', $text)) {
            $cod = 'rosu';
        } elseif (preg_match('/cod\s+portocaliu|portocalie/iu', $text)) {
            $cod = 'portocaliu';
        } elseif (preg_match('/cod\s+galben|galbenă/iu', $text)) {
            $cod = 'galben';
        }
    }
    
    if (!$cod) return null;
    
    // Extrage intervalul de valabilitate
    $valabilitate = extractWarningValidity($text);
    
    // Extrage fenomenele menționate
    $fenomene = extractPhenomena($text);
    
    // Extrage altitudinea dacă e menționată
    $altitudine = extractAltitude($text);
    
    return [
        'cod' => $cod,
        'text' => $text,
        'valabilitate' => $valabilitate,
        'fenomene' => $fenomene,
        'altitudine_minima' => $altitudine
    ];
}

/**
 * Extrage intervalul de valabilitate din text
 */
function extractWarningValidity($text) {
    $result = [
        'de_la' => null,
        'pana_la' => null,
        'text_original' => null
    ];
    
    // Pattern: "intervalul 28 decembrie, ora 10 – 29 decembrie, ora 02"
    if (preg_match('/intervalul\s+(\d{1,2})\s+(\w+),?\s*ora\s+(\d{1,2})\s*[–-]\s*(\d{1,2})\s+(\w+),?\s*ora\s+(\d{1,2})/iu', $text, $m)) {
        $result['text_original'] = $m[0];
        $result['de_la'] = parseRomanianDate($m[1], $m[2], $m[3]);
        $result['pana_la'] = parseRomanianDate($m[4], $m[5], $m[6]);
    }
    // Pattern: "valabil până la DD.MM.YYYY ora HH"
    elseif (preg_match('/valabil[ă]?\s+până\s+la\s+(\d{1,2})\.(\d{1,2})\.(\d{4}),?\s*ora\s+(\d{1,2})/iu', $text, $m)) {
        $result['text_original'] = $m[0];
        $result['pana_la'] = "{$m[3]}-{$m[2]}-{$m[1]}T{$m[4]}:00";
    }
    // Pattern: "valabil între ora X și ora Y"
    elseif (preg_match('/valabil[ă]?\s+între\s+ora\s+(\d{1,2})\s+și\s+ora\s+(\d{1,2})/iu', $text, $m)) {
        $result['text_original'] = $m[0];
        $today = date('Y-m-d');
        $result['de_la'] = "{$today}T{$m[1]}:00";
        $result['pana_la'] = "{$today}T{$m[2]}:00";
    }
    
    return $result;
}

/**
 * Parsează data în format românesc
 */
function parseRomanianDate($day, $month_name, $hour) {
    $months = [
        'ianuarie' => '01', 'februarie' => '02', 'martie' => '03',
        'aprilie' => '04', 'mai' => '05', 'iunie' => '06',
        'iulie' => '07', 'august' => '08', 'septembrie' => '09',
        'octombrie' => '10', 'noiembrie' => '11', 'decembrie' => '12'
    ];
    
    $month = $months[strtolower($month_name)] ?? '01';
    $year = date('Y');
    
    // Dacă luna e în trecut, probabil e anul viitor
    if (intval($month) < intval(date('m'))) {
        $year = date('Y') + 1;
    }
    
    return sprintf('%s-%s-%02d T%02d:00', $year, $month, intval($day), intval($hour));
}

/**
 * Extrage fenomenele meteo din text
 */
function extractPhenomena($text) {
    $fenomene = [];
    
    $patterns = [
        'viscol' => '/viscol|viscolit/iu',
        'ninsoare' => '/ninsoare|ninsori|ninge/iu',
        'vant' => '/vânt|rafale|vijelie/iu',
        'polei' => '/polei|gheață|îngheț/iu',
        'vizibilitate_redusa' => '/vizibilitate\s+(foarte\s+)?redusă/iu',
        'avalansa' => '/avalanș[eă]/iu',
        'ploaie' => '/ploaie|ploi|precipitații/iu',
        'furtuna' => '/furtun[ăi]|descărcări\s+electrice/iu',
        'grindina' => '/grindină/iu'
    ];
    
    foreach ($patterns as $fenomen => $pattern) {
        if (preg_match($pattern, $text)) {
            $fenomene[] = $fenomen;
        }
    }
    
    // Extrage viteza vântului dacă e menționată
    if (preg_match('/rafale\s+de\s+(\d+)\s*[–-]\s*(\d+)\s*km\/h/iu', $text, $m)) {
        $fenomene['vant_viteza'] = [
            'min' => intval($m[1]),
            'max' => intval($m[2])
        ];
    }
    
    return $fenomene;
}

/**
 * Extrage altitudinea minimă afectată
 */
function extractAltitude($text) {
    // Pattern: "la altitudini de peste 1.700 de metri"
    if (preg_match('/altitudini?\s+(?:de\s+)?(?:peste|mai\s+mari\s+de)\s+([\d.]+)\s*(?:de\s+)?m/iu', $text, $m)) {
        return intval(str_replace('.', '', $m[1]));
    }
    // Pattern: "peste 1700m"
    if (preg_match('/peste\s+([\d.]+)\s*m/iu', $text, $m)) {
        return intval(str_replace('.', '', $m[1]));
    }
    return null;
}

/**
 * Verifică dacă un masiv e afectat de o avertizare
 */
function isMasivAffected($masiv, $warning, $altitudine_tinta = null) {
    $zones = getZoneMapping();
    $masiv_zones = $zones[strtolower($masiv)] ?? [];
    
    if (empty($masiv_zones)) return false;
    
    $text_lower = strtolower($warning['text']);
    
    // Verifică dacă textul menționează zona masivului
    foreach ($masiv_zones as $zone) {
        if (strpos($text_lower, strtolower($zone)) !== false) {
            // Dacă avem altitudine țintă și avertizarea are altitudine minimă
            if ($altitudine_tinta && $warning['altitudine_minima']) {
                return $altitudine_tinta >= $warning['altitudine_minima'];
            }
            return true;
        }
    }
    
    // Verifică și pentru "zona montană" generic
    if (strpos($text_lower, 'zona montană') !== false || 
        strpos($text_lower, 'zonele montane') !== false ||
        strpos($text_lower, 'munți') !== false) {
        if ($altitudine_tinta && $warning['altitudine_minima']) {
            return $altitudine_tinta >= $warning['altitudine_minima'];
        }
        return true;
    }
    
    return false;
}

/**
 * Funcția principală - obține avertizările pentru un masiv
 */
function getNowcastingForMasiv($masiv, $altitudine_tinta = null, $data = null) {
    // Fetch pagina
    $fetch_result = fetchNowcastingWarnings();
    
    if (!$fetch_result['success']) {
        return [
            'success' => false,
            'error' => $fetch_result['error'],
            'cod_activ' => null
        ];
    }
    
    // Parsează avertizările
    $all_warnings = parseWarnings($fetch_result['html']);
    
    if (empty($all_warnings)) {
        return [
            'success' => true,
            'cod_activ' => null,
            'mesaj' => 'Nu sunt avertizări active',
            'avertizari' => []
        ];
    }
    
    // Filtrează pentru masivul nostru
    $relevant_warnings = [];
    foreach ($all_warnings as $warning) {
        if (isMasivAffected($masiv, $warning, $altitudine_tinta)) {
            $relevant_warnings[] = $warning;
        }
    }
    
    if (empty($relevant_warnings)) {
        return [
            'success' => true,
            'cod_activ' => null,
            'mesaj' => 'Nu sunt avertizări active pentru această zonă',
            'avertizari' => []
        ];
    }
    
    // Găsește cel mai sever cod
    $severity_order = ['rosu' => 3, 'portocaliu' => 2, 'galben' => 1];
    $max_severity = 0;
    $most_severe = null;
    
    foreach ($relevant_warnings as $warning) {
        $severity = $severity_order[$warning['cod']] ?? 0;
        if ($severity > $max_severity) {
            $max_severity = $severity;
            $most_severe = $warning;
        }
    }
    
    return [
        'success' => true,
        'cod_activ' => $most_severe['cod'],
        'cod_display' => formatCodDisplay($most_severe['cod']),
        'valabilitate' => $most_severe['valabilitate'],
        'fenomene' => $most_severe['fenomene'],
        'mesaj' => $most_severe['text'],
        'avertizari' => $relevant_warnings
    ];
}

/**
 * Formatează codul pentru afișare
 */
function formatCodDisplay($cod) {
    $display = [
        'rosu' => ['text' => 'Cod Roșu', 'culoare' => '#dc2626'],
        'portocaliu' => ['text' => 'Cod Portocaliu', 'culoare' => '#ea580c'],
        'galben' => ['text' => 'Cod Galben', 'culoare' => '#ca8a04']
    ];
    return $display[$cod] ?? ['text' => 'Necunoscut', 'culoare' => '#6b7280'];
}

// Endpoint direct (dacă e apelat ca API)
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    header('Content-Type: application/json; charset=utf-8');
    
    $masiv = isset($_GET['masiv']) ? strtolower(trim($_GET['masiv'])) : '';
    $altitudine = isset($_GET['altitudine']) ? intval($_GET['altitudine']) : null;
    $data = isset($_GET['data']) ? trim($_GET['data']) : null;
    
    if (empty($masiv)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Parametrul "masiv" e obligatoriu'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $result = getNowcastingForMasiv($masiv, $altitudine, $data);
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}