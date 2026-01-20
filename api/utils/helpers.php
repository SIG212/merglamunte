**
 * Analizează factorii evaluați și generează mesaj contextual dinamic
 * pentru context-card.js
 */
if (!function_exists('analizaContextDinamic')) {
    function analizaContextDinamic($factori, $meteo_status, $nivel_experienta = 'mediu', $altitudine_tinta = 1800) {
        // Colectează factorii periculoși
        $factori_critici = [];
        $factori_atentie = [];
        $factori_severi = []; // Pentru CAUTION dar foarte periculos (ex: avalanșă 3+)
        
        foreach ($factori as $nume_factor => $factor) {
            $nume_display = formatNumeFactor($nume_factor);
            
            if ($factor['status'] === 'ROSU') {
                $factori_critici[] = [
                    'nume' => $nume_display,
                    'detalii' => $factor['detalii']
                ];
            } elseif ($factor['status'] === 'GALBEN') {
                // SPECIAL: Avalanșă 3+ e foarte periculos chiar dacă e GALBEN
                if ($nume_factor === 'risc_avalansa') {
                    // Extrage nivelul din detalii (ex: "Risc 3/5")
                    if (preg_match('/Risc\s+(\d)/', $factor['detalii'], $matches)) {
                        $nivel_risc = intval($matches[1]);
                        if ($nivel_risc >= 3) {
                            $factori_severi[] = [
                                'nume' => $nume_display,
                                'detalii' => $factor['detalii']
                            ];
                        } else {
                            $factori_atentie[] = [
                                'nume' => $nume_display,
                                'detalii' => $factor['detalii']
                            ];
                        }
                    } else {
                        $factori_atentie[] = [
                            'nume' => $nume_display,
                            'detalii' => $factor['detalii']
                        ];
                    }
                } else {
                    $factori_atentie[] = [
                        'nume' => $nume_display,
                        'detalii' => $factor['detalii']
                    ];
                }
            }
        }
        
        // Determină mesajul principal și recomandările
        $conditii_text = '';
        $recomandari = [];
        
        // CAZ 1: Factori CRITICI (ROȘU)
        if (count($factori_critici) > 0) {
            $conditii_text = 'Condiții CRITICE - Pericole grave detectate';
            
            foreach ($factori_critici as $fc) {
                $recomandari[] = "⛔ {$fc['nume']}: {$fc['detalii']}";
            }
            
            $recomandari[] = "🚫 Amână drumeția sau alege un traseu alternativ la altitudine mai mică";
            $recomandari[] = "☎️ Verifică condițiile cu Salvamont înainte de plecare";
        }
        // CAZ 2: Factori SEVERI (avalanșă 3+) SAU 2+ factori GALBEN
        elseif (count($factori_severi) > 0 || count($factori_atentie) >= 2) {
            $conditii_text = 'Condiții DIFICILE - Necesită experiență și precauție sporită';
            
            // Listează factorii severi mai întâi
            foreach ($factori_severi as $fs) {
                $recomandari[] = "⚠️ {$fs['nume']}: {$fs['detalii']}";
            }
            
            // Apoi factorii de atenție
            foreach ($factori_atentie as $fa) {
                $recomandari[] = "⚠️ {$fa['nume']}: {$fa['detalii']}";
            }
            
            // Recomandări specifice pe nivel experiență
            if ($nivel_experienta === 'incepator') {
                $recomandari[] = "👥 Nivel începător: mergi DOAR cu ghid montan sau grup experimentat";
                $recomandari[] = "🔄 Alternativ: alege trasee marcate la altitudine sub 1500m";
            } else {
                $recomandari[] = "👥 Mergi în grup de minim 3 persoane";
                $recomandari[] = "📱 Informează pe cineva despre traseu și oră estimată de sosire";
            }
            
            $recomandari[] = "🔄 Fii pregătit să renunți dacă condițiile se înrăutățesc pe traseu";
        }
        // CAZ 3: UN singur factor GALBEN
        elseif (count($factori_atentie) === 1) {
            $conditii_text = 'Condiții ACCEPTABILE cu un factor de atenție';
            
            $fa = $factori_atentie[0];
            $recomandari[] = "⚠️ {$fa['nume']}: {$fa['detalii']}";
            $recomandari[] = "✅ Restul condițiilor sunt favorabile";
            $recomandari[] = "👁️ Monitorizează acest factor pe parcursul traseului";
        }
        // CAZ 4: TOTUL OK (VERDE)
        else {
            $conditii_text = 'Condiții BUNE pentru drumeție';
            $recomandari[] = "✅ Toate condițiile meteo sunt favorabile";
            $recomandari[] = "🎯 Respectă în continuare regulile de siguranță în munte";
            $recomandari[] = "📱 Ține telefonul încărcat pentru eventuale urgențe";
            
            if ($altitudine_tinta > 2000) {
                $recomandari[] = "⛰️ Altitudine {$altitudine_tinta}m: condițiile se pot schimba rapid";
            }
        }
        
        return [
            'conditii_text' => $conditii_text,
            'recomandari' => $recomandari,
            'factori_critici_count' => count($factori_critici),
            'factori_atentie_count' => count($factori_atentie),
            'factori_severi_count' => count($factori_severi)
        ];
    }
}

/**
 * Formatează numele factorului pentru afișare
 */
if (!function_exists('formatNumeFactor')) {
    function formatNumeFactor($nume_factor) {
        $mapping = [
            'stres_termic' => 'Stres Termic (Windchill)',
            'vant' => 'Vânt',
            'vizibilitate' => 'Vizibilitate',
            'precipitatii_ninsoare' => 'Ninsoare',
            'precipitatii_ploaie' => 'Ploaie',
            'precipitatii_lapovita' => 'Lapoviță',
            'precipitatii_inghet' => 'Chiciură/Polei',
            'instabilitate_atmosferica' => 'Risc Furtuni',
            'stare_sol' => 'Starea Solului',
            'durata_expunere' => 'Durată Expunere',
            'schimbari_rapide' => 'Schimbări Meteo Rapide',
            'risc_avalansa' => 'Risc Avalanșă'
        ];
        
        return $mapping[$nume_factor] ?? ucfirst(str_replace('_', ' ', $nume_factor));
    }
}
